#include "GamePage.h"

#include "message.h"


#include "minorGems/util/stringUtils.h"



int GamePage::sPageCount = 0;

SpriteHandle GamePage::sWaitingSprites[3] = { NULL, NULL, NULL };
int GamePage::sCurrentWaitingSprite = 0;
int GamePage::sLastWaitingSprite = -1;
int GamePage::sWaitingSpriteDirection = 1;
double GamePage::sCurrentWaitingSpriteFade = 0;

double GamePage::sWaitingFade = 0;
char GamePage::sWaiting = false;



GamePage::GamePage()
        : PageComponent( 0, 0 ),
          mStatusError( false ),
          mStatusMessageKey( NULL ),
          mStatusMessage( NULL ),
          mTip( NULL ) {

    if( sWaitingSprites[0] == NULL ) {
        sWaitingSprites[0] = loadSprite( "loading.tga", true );
        sWaitingSprites[1] = loadSprite( "loading2.tga", true );
        sWaitingSprites[2] = loadSprite( "loading3.tga", true );
        }
    sPageCount++;
    }




GamePage::~GamePage() {
    if( mStatusMessage != NULL ) {
        delete [] mStatusMessage;
        }
    if( mTip != NULL ) {
        delete [] mTip;
        }
    sPageCount--;
    if( sPageCount == 0 ) {
        freeSprite( sWaitingSprites[0] );
        freeSprite( sWaitingSprites[1] );
        freeSprite( sWaitingSprites[2] );
        
        sWaitingSprites[0] = NULL;
        sWaitingSprites[1] = NULL;
        sWaitingSprites[2] = NULL;
        }
    }



void GamePage::setStatus( const char *inStatusMessageKey, char inError ) {
    mStatusMessageKey = inStatusMessageKey;
    mStatusError = inError;

    if( mStatusMessage != NULL ) {
        delete [] mStatusMessage;
        mStatusMessage = NULL;
        }
    }



void GamePage::setStatusDirect( char *inStatusMessage, char inError ) {
    if( mStatusMessage != NULL ) {
        delete [] mStatusMessage;
        mStatusMessage = NULL;
        }
    
    if( inStatusMessage != NULL ) {
        mStatusMessage = stringDuplicate( inStatusMessage );
        
        mStatusMessageKey = NULL;
        }
    
    mStatusError = inError;
    }



void GamePage::setToolTip( const char *inTip ) {
    if( mTip != NULL ) {
        delete [] mTip;
        }
    mTip = stringDuplicate( inTip );
    }




void GamePage::base_draw( doublePair inViewCenter, 
                          double inViewSize ){
    
    PageComponent::base_draw( inViewCenter, inViewSize );
    

    if( mStatusMessageKey != NULL ) {
        doublePair labelPos = { 0, -5 };
        
        drawMessage( mStatusMessageKey, labelPos, mStatusError );
        }
    else if( mStatusMessage != NULL ) {
        doublePair labelPos = { 0, -5 };
        
        drawMessage( mStatusMessage, labelPos, mStatusError );
        }


    if( mTip != NULL ) {
        doublePair labelPos = { 0, -7 };
        
        drawMessage( mTip, labelPos );
        }

    if( sWaitingFade > 0 ) {
        
        doublePair spritePos = { 9.25, 7 };

        setDrawColor( 1, 1, 1, 
                      sWaitingFade * sCurrentWaitingSpriteFade );

        drawSprite( sWaitingSprites[sCurrentWaitingSprite], 
                    spritePos, 1/16.0 );

        if( sLastWaitingSprite != -1 ) {
            
            setDrawColor( 1, 1, 1, 
                          sWaitingFade * ( 1 - sCurrentWaitingSpriteFade ) );
            
            drawSprite( sWaitingSprites[sLastWaitingSprite], 
                        spritePos, 1/16.0 );
            }
        }
    
    draw( inViewCenter, inViewSize );
    }


extern double frameRateFactor;


void GamePage::base_step() {
    PageComponent::base_step();

    if( sWaiting ) {
        sWaitingFade += 0.05 * frameRateFactor;
    
        if( sWaitingFade > 1 ) {
            sWaitingFade = 1;
            }

        }
    else {
        sWaitingFade -= 0.05 * frameRateFactor;
        if( sWaitingFade < 0 ) {
            sWaitingFade = 0;
            }
        }
    
    // skip animation if not visible
    if( sWaitingFade > 0 ) {
        
        sCurrentWaitingSpriteFade += 0.025 * frameRateFactor;
        if( sCurrentWaitingSpriteFade > 1 ) {
            sCurrentWaitingSpriteFade = 0;

            switch( sCurrentWaitingSprite ) {
                case 0:
                    sCurrentWaitingSprite = 1;
                    sLastWaitingSprite = 0;
                    sWaitingSpriteDirection = 1;
                    break;
                case 1:
                    sCurrentWaitingSprite += sWaitingSpriteDirection;
                    sLastWaitingSprite = 1;
                    break;
                case 2:
                    sCurrentWaitingSprite = 1;
                    sLastWaitingSprite = 2;
                    sWaitingSpriteDirection = -1;
                    break;
                }
            }
        }
    else {
        // reset animation
        sCurrentWaitingSprite = 0;
        sLastWaitingSprite = -1;
        sCurrentWaitingSpriteFade = 0;
        sWaitingSpriteDirection = 1;
        }
        
    }




void GamePage::base_makeActive( char inFresh ){
    if( inFresh ) {    
        for( int i=0; i<mComponents.size(); i++ ) {
            PageComponent *c = *( mComponents.getElement( i ) );
            
            c->base_clearState();
            }
        }
    

    makeActive( inFresh );
    }



void GamePage::base_makeNotActive(){
    for( int i=0; i<mComponents.size(); i++ ) {
        PageComponent *c = *( mComponents.getElement( i ) );
        
        c->base_clearState();
        }
    
    makeNotActive();
    }




void GamePage::setWaiting( char inWaiting ) {
    sWaiting = inWaiting;
    }
    




