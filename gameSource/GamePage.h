#ifndef GAME_PAGE_INCLUDED
#define GAME_PAGE_INCLUDED


#include "minorGems/game/doublePair.h"

class GamePage {
        

    public:
        

        virtual ~GamePage();
        

        virtual void step() = 0;
        
        virtual void draw( doublePair inViewCenter, 
                           double inViewSize ) = 0;
        
        // inFresh set to true when returning to this page
        // after visiting other pages
        // set to false after returning from pause.
        virtual void makeActive( char inFresh ) = 0;
        virtual void makeNotActive() = 0;


        // default implementations do nothing
        // sub classes implement whichever they need
        virtual void pointerMove( float inX, float inY ) {
            };

        virtual void pointerDown( float inX, float inY ) {
            };

        virtual void pointerDrag( float inX, float inY ) {
            };

        virtual void pointerUp( float inX, float inY ) {
            };

        virtual void keyDown( unsigned char inASCII ) {
            };
        
        virtual void keyUp( unsigned char inASCII ) {
            };

        virtual void specialKeyDown( int inKeyCode ) {
            };

        virtual void specialKeyUp( int inKeyCode ) {
            };
        



    protected:
        
        GamePage();

    };


#endif

