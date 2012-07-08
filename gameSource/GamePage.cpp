#include "GamePage.h"

#include "message.h"


#include "minorGems/util/stringUtils.h"




GamePage::GamePage()
        : PageComponent( 0, 0 ),
          mStatusError( false ),
          mStatusMessageKey( NULL ),
          mStatusMessage( NULL ),
          mTipKey( NULL ),
          mTip( NULL ) {
    }



GamePage::~GamePage() {
    if( mStatusMessage != NULL ) {
        delete [] mStatusMessage;
        }
    if( mTip != NULL ) {
        delete [] mTip;
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



void GamePage::setToolTip( const char *inTipKey ) {
    mTipKey = inTipKey;

    if( mTip != NULL ) {
        delete [] mTip;
        mTip = NULL;
        }
    }



void GamePage::setToolTipDirect( const char *inTip ) {
    if( mTip != NULL ) {
        delete [] mTip;
        mTip = NULL;
        }
    
    if( inTip != NULL ) {
        mTip = stringDuplicate( inTip );
        
        mTipKey = NULL;
        }
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


    if( mTipKey != NULL ) {
        doublePair labelPos = { 0, -7 };
        
        drawMessage( mTipKey, labelPos );
        }
    else if( mTip != NULL ) {
        doublePair labelPos = { 0, -7 };
        
        drawMessage( mTip, labelPos );
        }

    draw( inViewCenter, inViewSize );
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





