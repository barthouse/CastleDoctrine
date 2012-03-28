#include "LiveHousePage.h"

#include "TextField.h"
#include "TextButton.h"

#include "HouseGridDisplay.h"


#include "minorGems/ui/event/ActionListener.h"



class EditHousePage : public LiveHousePage, public ActionListener {
        
    public:
        
        EditHousePage();
        
        virtual ~EditHousePage();
        
        
        // destroyed by caller
        void setHouseMap( char *inHouseMap );
        char *getHouseMap();
        
        // true if map changed since last setHouseMap call
        char houseMapChanged();
        

        char getDone() {
            return mDone;
            }
            

        virtual void actionPerformed( GUIComponent *inTarget );


        virtual void draw( doublePair inViewCenter, 
                           double inViewSize );
        
        
        virtual void makeActive( char inFresh );

    protected:

        // for change detection
        char *mStartHouseMap;
        

        HouseGridDisplay mGridDisplay;
        TextButton mDoneButton;
        
        char mDone;

    };

