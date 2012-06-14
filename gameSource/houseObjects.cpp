#include "houseObjects.h"

#include "gameElements.h"

#include "minorGems/util/SimpleVector.h"

#include "minorGems/io/file/File.h"



// redefine F so that it expands each name into a string constant
#undef F
#define F(inName) #inName

const char *propertyIDNames[] = {
	PROPERTY_NAMES
    };



// returns endPropertyID if mapping fails
static propertyID mapNameToID( const char *inName ) {
    for( int i = 0; i < endPropertyID; i++ ) {
        
        if( strcmp( inName, propertyIDNames[i] ) == 0 ) {
            return (propertyID)i;
            }    
        }

    return endPropertyID;
    }



typedef struct houseObjectState {

        // can be 0 if state not present
        int numOrientations;
        
        SpriteHandle stateSprite[ MAX_ORIENTATIONS ];
        
        char behindSpritePresent;

        SpriteHandle stateSpriteBehind[ MAX_ORIENTATIONS ];
        
        char properties[ endPropertyID ];

        char *subDescription;

    } houseObjectState;



typedef struct houseObjectRecord {
        int id;
        
        char *name;
        
        char *description;
        
        int numStates;
        
        
        // sprites can be absent for certain entries if state IDs 
        // are sparsely distributed
        // (absent sprites have 0 orientations)
        //
        // MUST have at least 0 defined
        houseObjectState *states;
        

    } houseObjectRecord;



static SimpleVector<houseObjectRecord> objects;

static int idSpaceSize = 0;

// some may be -1
static int *idToIndexMap = NULL;



static houseObjectState readState( File *inStateDir ) {
    
    int numChildFiles;
    File **childFiles = inStateDir->getChildFiles( &numChildFiles );
    
    char *tgaPath = NULL;
    char *behindTgaPath = NULL;
    char *propertiesContents = NULL;
    char *subInfoContents = NULL;

    char transCorner = true;

    for( int i=0; i<numChildFiles; i++ ) {
        
        File *f = childFiles[i];
        
        char *name = f->getFileName();

        if( strstr( name, "_behind.tga" ) != NULL ) {
            if( behindTgaPath != NULL ) {
                delete [] behindTgaPath;
                }
            behindTgaPath = f->getFullFileName();
            }
        else if( strstr( name, ".tga" ) != NULL ) {
            if( tgaPath != NULL ) {
                delete [] tgaPath;
                }
            tgaPath = f->getFullFileName();
            }
        else if( strcmp( name, "properties.txt" ) == 0 ) {
            if( propertiesContents != NULL ) {
                delete [] propertiesContents;
                }
            propertiesContents = f->readFileContents();
            }
        else if( strcmp( name, "subInfo.txt" ) == 0 ) {
            if( subInfoContents != NULL ) {
                delete [] subInfoContents;
                }
            subInfoContents = f->readFileContents();
            }
        
        delete [] name;

        delete childFiles[i];
        }
    delete [] childFiles;

    
    houseObjectState state;

    state.numOrientations = 0;
    state.behindSpritePresent = false;
    state.subDescription = NULL;

    // init property array, all off
    for( int p=0; p<endPropertyID; p++ ) {
        state.properties[p] = false;
        }
    
    // next, read properties.txt file and set flags
    
    if( propertiesContents != NULL ) {
        
        
        SimpleVector<char *> *tokens = 
            tokenizeString( propertiesContents );
        
        
        for( int t=0; t<tokens->size(); t++ ) {
            char *name = *( tokens->getElement(t) );
            
            propertyID p = mapNameToID( name );
            
            if( p != endPropertyID ) {
                state.properties[p] = true;
                }

            delete [] name;    
            }
        delete tokens;


        delete [] propertiesContents;
        }


    
    if( subInfoContents != NULL ) {
        char *info = subInfoContents;
        
        // skip the first "
        int readChar = ' ';

        while( readChar != '"' && readChar != '\0' ) {
            readChar = info[0];
            info = &( info[1] );
            }

                
        char *descriptionString = new char[1000];
        // scan a string of up to 999 characters, stopping
        // at the first " character
        int numRead = sscanf( info, "%999[^\"]",
                              descriptionString );
        
        if( numRead == 1 ) {
            state.subDescription = stringDuplicate( descriptionString );
            }

        delete [] descriptionString;
        
        delete [] subInfoContents;
        }


    if( tgaPath == NULL ) {
        return state;
        }
    
    
    printf( "Trying to read tga from %s\n", tgaPath );

    Image *image = readTGAFileBase( tgaPath );
    delete [] tgaPath;

    if( image == NULL ) {    
        return state;
        }
    

    int fullH = image->getHeight();
    int fullW = image->getWidth();

    int tileH = fullW;

    state.numOrientations = fullH / tileH;
    
    printf( "  Reading %d orientations\n", state.numOrientations );

    for( int o=0; o<state.numOrientations; o++ ) {
        
        Image *subImage = image->getSubImage( 0, tileH * o,
                                              fullW, tileH );
        
        state.stateSprite[o] = fillSprite( subImage, transCorner );
        
        delete subImage;
        }

    delete image;
    

    

    if( behindTgaPath == NULL ) {
        return state;
        }
    


    printf( "Trying to read behind-image tga from %s\n", behindTgaPath );

    image = readTGAFileBase( behindTgaPath );

    delete [] behindTgaPath;

    if( image == NULL ) {
        return state;
        }
    

    fullH = image->getHeight();
    fullW = image->getWidth();

    tileH = fullW;

    int numOrientationsPresent = fullH / tileH;
    
    if( numOrientationsPresent != state.numOrientations ) {
        printf( "  Orientations (%d) doesn't match "
                "what is in front TGA (%d)\n",
                numOrientationsPresent, state.numOrientations );
        delete image;
        return state;
        }

    printf( "  Reading %d orientations\n", state.numOrientations );

    for( int o=0; o<state.numOrientations; o++ ) {
        
        Image *subImage = image->getSubImage( 0, tileH * o,
                                              fullW, tileH );
        
        state.stateSpriteBehind[o] = fillSprite( subImage, transCorner );
        
        delete subImage;
        }

    delete image;

    state.behindSpritePresent = true;

    

    return state;
    }




void initHouseObjects() {
    File elementsDir( NULL, "gameElements" );

    if( !elementsDir.exists() || !elementsDir.isDirectory() ) {
        return;
        }
    
    File *objectsDir = elementsDir.getChildFile( "houseObjects" );
    
    if( objectsDir == NULL ) {
        return;
        }
    else if( !objectsDir->exists() || !objectsDir->isDirectory() ) {
        delete objectsDir;
        return;
        }
    
    int numObjects;
    File **objectNameDirs = objectsDir->getChildFiles( &numObjects );

    delete objectsDir;
    
    if( objectNameDirs == NULL ) {
        return;
        }
    
    
    for( int i=0; i<numObjects; i++ ) {
        
        File *f = objectNameDirs[i];
        

        if( f->exists() && f->isDirectory() ) {
            
            char completeRecord = true;

            houseObjectRecord r;
            
            r.name = f->getFileName();
            r.description = NULL;

            File *infoFile = f->getChildFile( "info.txt" );
            
            completeRecord = readInfoFile( infoFile, 
                                           &( r.id ), &( r.description ) );
                        
            delete infoFile;


            if( completeRecord ) {
                
                // read states

                int numStateDirs;
                File **stateDirs = f->getChildFiles( &numStateDirs );

                // look for maximum defined state directory
                // then make a sparsely-filled array of states
                // that's big enough to contain that max index number
                int maxStateNumber = 0;

                for( int c=0; c<numStateDirs; c++ ) {
                    if( stateDirs[c]->isDirectory() ) {
                        
                        char *dirName = stateDirs[c]->getFileName();
                        
                        int number;
                        
                        int numRead = sscanf( dirName, "%d", &number );
                        
                        if( numRead == 1 ) {
                            
                            // make sure that dir name is pure state number
                            // skip it, otherwise (i.e., skip:  "test0")

                            char *checkDirName = autoSprintf( "%d", number );
                            
                            if( strcmp( checkDirName, dirName ) == 0 ) {
                                

                                if( maxStateNumber < number ) {
                                    maxStateNumber = number;
                                    }
                                }
                            delete [] checkDirName;
                            }
                        delete [] dirName;
                        }
                    delete stateDirs[c];
                    }
                delete [] stateDirs;
                
                
                r.numStates = maxStateNumber + 1;
                
                r.states = new houseObjectState[ r.numStates ];
                

                for( int s=0; s<r.numStates; s++ ) {
                    // some indexed states might not be present
                    
                    // set numOrientations to zero to mark non-present states
                    r.states[s].numOrientations = 0;
                    
                    char *stateDirName = autoSprintf( "%d", s );
                    
                    File *stateDir = f->getChildFile( stateDirName );
                    
                    
                    if( stateDir->exists() && stateDir->isDirectory() ) {
                        
                        r.states[s] = readState( stateDir );
                        }

                    delete stateDir;

                    delete [] stateDirName;                    
                    }
                
                
                if( r.id >= idSpaceSize ) {
                    idSpaceSize = r.id + 1;
                    }

                objects.push_back( r );
                }
            else {
                delete [] r.name;
                if( r.description != NULL ) {
                    delete [] r.description;
                    }
                }
            }

        delete f;
        }

    delete [] objectNameDirs;


    // build map
    idToIndexMap = new int[idSpaceSize];
    for( int i=0; i<idSpaceSize; i++ ) {
        idToIndexMap[i] = -1;
        }

    for( int i=0; i<objects.size(); i++ ) {
        houseObjectRecord r = *( objects.getElement( i ) );
        
        idToIndexMap[r.id] = i;
        }
    
    }



void freeHouseObjects() {
    for( int i=0; i<objects.size(); i++ ) {
        houseObjectRecord r = *( objects.getElement( i ) );
        
        
        delete [] r.name;
        delete [] r.description;
        
        for( int s=0; s<r.numStates; s++ ) {

            for( int o=0; o<r.states[s].numOrientations; o++ ) {
                freeSprite( r.states[s].stateSprite[o] );
                if( r.states[s].behindSpritePresent ) {
                    freeSprite( r.states[s].stateSpriteBehind[o] );
                    }
                if( r.states[s].subDescription != NULL ) {
                    delete [] r.states[s].subDescription;
                    }
                }
            }
        delete [] r.states;
        }

    objects.deleteAll();
    
    if( idToIndexMap != NULL ) {
        delete [] idToIndexMap;
        
        idToIndexMap = NULL;
        }
    }



int *getFullObjectIDList( int *outNumIDs ) {
    *outNumIDs = objects.size();
    
    int *returnList = new int[ *outNumIDs ];
    
    
    for( int i=0; i<*outNumIDs; i++ ) {
        houseObjectRecord *r = objects.getElement( i );
    
        returnList[i] = r->id;
        }
    
    return returnList;
    }




static houseObjectState *getObjectState( int inObjectID, int inState ) {

    int index = idToIndexMap[inObjectID];
    
    houseObjectRecord *r = objects.getElement( index );

    if( inState >= r->numStates ) {
        // switch to default state
        inState = 0;
        }

    houseObjectState *returnState = &( r->states[inState] );
    
    if( returnState->numOrientations == 0 ) {
        // not actually present (sparse array), switch to default

        returnState = &( r->states[0] );
        }
    

    return returnState;
    }




const char *getObjectName( int inObjectID ) {
    houseObjectRecord *r = objects.getElement( idToIndexMap[inObjectID] );
    
    return r->name;
    }



const char *getObjectDescription( int inObjectID, int inState ) {
    houseObjectState *state = getObjectState( inObjectID, inState );
    
    if( state->subDescription != NULL ) {
        // state-specific description present
        return state->subDescription;
        }
    else {
        // default, universal description
        houseObjectRecord *r = objects.getElement( idToIndexMap[inObjectID] );
        
        return r->description;
        }

    }




int getObjectID( const char *inName ) {
    for( int i=0; i<objects.size(); i++ ) {
        houseObjectRecord *r = objects.getElement( i );
        
        if( strcmp( r->name, inName ) == 0 ) {
            return r->id;
            }
        } 

    return -1;
    }







SpriteHandle getObjectSprite( int inObjectID, 
                              int inOrientation, int inState ) {
    
    houseObjectState *state = getObjectState( inObjectID, inState );

    if( inOrientation >= state->numOrientations ) {
        // default
        inOrientation = 0;
        }    

    return state->stateSprite[inOrientation];
    }



SpriteHandle getObjectSpriteBehind( int inObjectID, 
                                    int inOrientation, int inState ) {
    
    houseObjectState *state = getObjectState( inObjectID, inState );

    if( inOrientation >= state->numOrientations ) {
        // default
        inOrientation = 0;
        }    

    return state->stateSpriteBehind[inOrientation];
    }




int getNumOrientations( int inObjectID, int inState ) {
    houseObjectState *state = getObjectState( inObjectID, inState );

    return state->numOrientations;
    }



char isBehindSpritePresent( int inObjectID, int inState ) {
    houseObjectState *state = getObjectState( inObjectID, inState );

    return state->behindSpritePresent;
    }



char isPropertySet( int inObjectID, int inState, propertyID inProperty ) {
    houseObjectState *state = getObjectState( inObjectID, inState );

    
    return state->properties[ inProperty ];
    }


