#include "CheckinHousePage.h"
#include "ticketHash.h"
#include "message.h"


#include "minorGems/game/Font.h"
#include "minorGems/game/game.h"

#include "minorGems/util/stringUtils.h"


extern Font *mainFont;


extern char *serverURL;

extern int userID;



CheckinHousePage::CheckinHousePage() 
        : mWebRequest( -1 ),
          mHouseMap( NULL ),
          mEditList( NULL ),
          mPriceList( NULL ),
          mDied( 0 ),
          mMenuButton( mainFont, 4, -4, translate( "returnMenu" ) ),
          mStartOverButton( mainFont, 4, -4, translate( "startOver" ) ),
          mReturnToMenu( false ),
          mStartOver( true ) {

    addComponent( &mMenuButton );
    mMenuButton.addActionListener( this );

    addComponent( &mStartOverButton );
    mStartOverButton.addActionListener( this );
    }


        
CheckinHousePage::~CheckinHousePage() {
    if( mWebRequest != -1 ) {
        clearWebRequest( mWebRequest );
        }
    if( mHouseMap != NULL ) {
        delete [] mHouseMap;
        }
    if( mEditList != NULL ) {
        delete [] mEditList;
        }
    if( mPriceList != NULL ) {
        delete [] mPriceList;
        }
    }



char CheckinHousePage::getReturnToMenu() {
    return mReturnToMenu;
    }


char CheckinHousePage::getStartOver() {
    return mStartOver;
    }



void CheckinHousePage::setHouseMap( char *inHouseMap ) {
    if( mHouseMap != NULL ) {
        delete [] mHouseMap;
        }
    mHouseMap = stringDuplicate( inHouseMap );
    }



void CheckinHousePage::setEditList( char *inEditList ) {
    if( mEditList != NULL ) {
        delete [] mEditList;
        }
    mEditList = stringDuplicate( inEditList );
    }



void CheckinHousePage::setPriceList( char *inPriceList ) {
    if( mPriceList != NULL ) {
        delete [] mPriceList;
        }
    mPriceList = stringDuplicate( inPriceList );
    }



void CheckinHousePage::setDied( int inDied ) {
    mDied = inDied;
    }



void CheckinHousePage::actionPerformed( GUIComponent *inTarget ) {
    if( inTarget == &mMenuButton ) {
        mReturnToMenu = true;
        }
    else if( inTarget == &mStartOverButton ) {
        mStartOver = true;
        }
    }


void CheckinHousePage::step() {
    if( mWebRequest != -1 ) {
            
        int stepResult = stepWebRequest( mWebRequest );

        switch( stepResult ) {
            case 0:
                break;
            case -1:
                mStatusError = true;
                mStatusMessageKey = "err_webRequest";
                clearWebRequest( mWebRequest );
                mWebRequest = -1;
                mMenuButton.setVisible( true );
                break;
            case 1: {
                char *result = getWebResult( mWebRequest );
                clearWebRequest( mWebRequest );
                mWebRequest = -1;
                     
                printf( "Web result = %s\n", result );
   
                if( strstr( result, "OK" ) != NULL ) {
                    // house checked in!
                    
                    if( mDied == 0 ) {    
                        mStatusError = false;
                        mStatusMessageKey = "houseCheckedIn";
                        mReturnToMenu = true;
                        }
                    else {
                        mStatusError = true;
                        mStatusMessageKey = "deathMessage";
                        mStartOverButton.setVisible( true );
                        }
                    }
                else {
                    mStatusError = true;
                    mStatusMessageKey = "houseCheckInFailed";
                    mMenuButton.setVisible( true );
                    }
                
                        
                        
                delete [] result;
                }
                break;
            }
        }
    }



        
void CheckinHousePage::makeActive( char inFresh ) {
    if( !inFresh ) {
        return;
        }
    
    // send back to server            
    char *ticketHash = getTicketHash();
        
            
    
    char *actionString = autoSprintf( 
        "action=end_edit_house&user_id=%d"
        "&%s&died=%d&house_map=%s&price_list=%s&edit_list=%s",
        userID, ticketHash, mDied, mHouseMap, mPriceList, mEditList );
    delete [] ticketHash;
            
    
    mWebRequest = startWebRequest( "POST", 
                                   serverURL, 
                                   actionString );
    
    printf( "Starting web request %s %s\n", serverURL, actionString );

    delete [] actionString;

    mReturnToMenu = false;
    mStartOver = false;
    
    mStatusError = false;
    mStatusMessageKey = NULL;

    mMenuButton.setVisible( false );
    mStartOverButton.setVisible( false );
    }


