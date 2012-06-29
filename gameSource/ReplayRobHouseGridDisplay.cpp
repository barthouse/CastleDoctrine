#include "ReplayRobHouseGridDisplay.h"


#include "minorGems/game/Font.h"
#include "minorGems/game/game.h"

#include "minorGems/util/stringUtils.h"


extern Font *mainFont;

extern double frameRateFactor;


#define STEP_DELAY (int)( 30 * frameRateFactor )

#define BUTTON_X -8



ReplayRobHouseGridDisplay::ReplayRobHouseGridDisplay( double inX, double inY )
        : RobHouseGridDisplay( inX, inY ),
          mStepButton( mainFont, BUTTON_X, -5, 
                       translate( "step" ) ),
          mPlayButton( mainFont, BUTTON_X, -3.5, 
                       translate( "play" ) ),
          mStopButton( mainFont, BUTTON_X, -2, 
                       translate( "stop" ) ),
          mVisibilityButton( mainFont, BUTTON_X, 5, 
                             translate( "toggleVisibility" ) ),
          mPlaying( false ),
          mStepsUntilNextPlayStep( 0 ),
          mVisibilityToggle( false ),
          mToolIDJustPicked( -1 ) {

    mStopButton.setVisible( false );
    
    addComponent( &mStepButton );
    addComponent( &mPlayButton );
    addComponent( &mStopButton );
    addComponent( &mVisibilityButton );
    
    mStepButton.addActionListener( this );
    mPlayButton.addActionListener( this );
    mStopButton.addActionListener( this );
    mVisibilityButton.addActionListener( this );
    }


        
ReplayRobHouseGridDisplay::~ReplayRobHouseGridDisplay() {

    }



void ReplayRobHouseGridDisplay::setMoveList( char *inMoveList ) {
    clearMoveList();
    
    int numMoves;
    char **moves = split( inMoveList, "#", &numMoves );

    mMoveList.appendArray( moves, numMoves );
    
    delete [] moves;

    // prepare for new playback
    mPlaying =  false;
        
    mStepButton.setVisible( true );
    mPlayButton.setVisible( true );
    mStopButton.setVisible( false );
    }



int ReplayRobHouseGridDisplay::getToolIDJustPicked() {
    int temp = mToolIDJustPicked;
    mToolIDJustPicked = -1;

    return temp;
    }



void ReplayRobHouseGridDisplay::step() {
    RobHouseGridDisplay::step();
    
    if( mPlaying ) {
        mStepsUntilNextPlayStep --;
        
        if( mStepsUntilNextPlayStep == 0 ) {
            takeStep();
            
            mStepsUntilNextPlayStep = STEP_DELAY;
            }
        }
    
    }



void ReplayRobHouseGridDisplay::takeStep() {
    if( mMoveList.size() > 0 ) {
        
        char *move = *( mMoveList.getElement( 0 ) );
        
        char shouldDeleteMove = true;

        if( strlen( move ) > 0 ) {
            if( move[0] == 'm' ) {
                // player movement to new index
                int newIndex;
                sscanf( move, "m%d", &newIndex );

                // DON'T call RobHouseGridDisplay's moveRobber, 
                // because it detects success condition and fires an event,
                // which we don't want to do during replay
                HouseGridDisplay::moveRobber( newIndex );
                
                shouldDeleteMove = true;
                
                applyTransitionsAndProcess();
                }
            else if( move[0] == 't' ) {
                int toolID, targetIndex;
                
                sscanf( move, "t%d@%d", &toolID, &targetIndex );
                
                if( mCurrentTool == -1 ) {
                    
                    // display that tool is being used
                    startUsingTool( toolID );
                    mToolIDJustPicked = toolID;
                    fireActionPerformed( this );
                    
                    shouldDeleteMove = false;
                    }
                else {
                    // already started using tool last step
                    
                    // actually use it this step

                    applyCurrentTool( targetIndex );
                    fireActionPerformed( this );

                    shouldDeleteMove = true;
                    }
                }
            }

        if( shouldDeleteMove ) {
            mMoveList.deleteElement( 0 );
            
            delete [] move;
            }
        
        }

    if( mMoveList.size() == 0 ) {
        // auto stop, no more steps allowed
        mPlaying =  false;
        
        mStepButton.setVisible( false );
        mPlayButton.setVisible( false );
        mStopButton.setVisible( false );
        }
    }




void ReplayRobHouseGridDisplay::specialKeyDown( int inKeyCode ) {
    // do nothing (ignore keyboard input)
    }

        
        
void ReplayRobHouseGridDisplay::actionPerformed( GUIComponent *inTarget ) {
    if( inTarget == &mPlayButton ) {
        mPlaying = true;
        mStepsUntilNextPlayStep = STEP_DELAY;     

        mStopButton.setVisible( true );
        mPlayButton.setVisible( false );
        mStepButton.setVisible( false );
       }
    else if( inTarget == &mStopButton ) {
        mPlaying = false;
        mStopButton.setVisible( false );
        mPlayButton.setVisible( true );
        mStepButton.setVisible( true );
        }
    else if( inTarget == &mStepButton ) {
        takeStep();
        }
    else if( inTarget == &mVisibilityButton ) {
        mVisibilityToggle = ! mVisibilityToggle;
        recomputeVisibility();
        }
    }



void ReplayRobHouseGridDisplay::recomputeVisibility() {
    
    if( mVisibilityToggle ) {    
        // all visible during playback

        int i = 0;
        for( int y=0; y<HOUSE_D * VIS_BLOWUP; y++ ) {
            for( int x=0; x<HOUSE_D * VIS_BLOWUP; x++ ) {

                mTargetVisibleMap[i] = true;

                i++;
                }
            }
        }
    else {
        RobHouseGridDisplay::recomputeVisibility();
        }
    
    }

