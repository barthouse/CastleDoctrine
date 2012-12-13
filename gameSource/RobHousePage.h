#include "LiveHousePage.h"

#include "TextField.h"
#include "TextButton.h"

#include "RobHouseGridDisplay.h"

#include "Gallery.h"

#include "inventory.h"



#include "minorGems/ui/event/ActionListener.h"
#include "minorGems/util/random/CustomRandomSource.h"



class RobHousePage : public LiveHousePage, public ActionListener {
        
    public:
        
        RobHousePage( const char *inDoneButtonKey = "doneRob" );
        
        virtual ~RobHousePage();
        

        // set to false to hide backpack buttons
        // defaults to showing them
        void showBackpack( char inShow );
        
        
        // destroyed by caller
        void setHouseMap( char *inHouseMap );
        char *getHouseMap();

        // destroyed by caller
        void setBackpackContents( char *inBackpackContents );
        char *getBackpackContents();

        void setGalleryContents( char *inGalleryContents );
        
        void setMusicSeed( int inMusicSeed );

        char getSuccess() {
            return mGridDisplay.getSuccess();
            }

        char *getMoveList() {
            return mGridDisplay.getMoveList();
            }
        
        char getWifePresent() {
            return mGridDisplay.getWifePresent();
            }

        char getWifeRobbed() {
            return mGridDisplay.getWifeRobbed();
            }
        
        char getAnyFamilyKilled() {
            return mGridDisplay.getAnyFamilyKilled();
            }


        char getDone() {
            return mDone;
            }
            
        void setDescription( const char *inDescription );
        


        virtual void actionPerformed( GUIComponent *inTarget );


        virtual void draw( doublePair inViewCenter, 
                   double inViewSize );

        
        virtual void makeActive( char inFresh );

    protected:

        CustomRandomSource mRandSource;
        
        char mShowBackpack;

        RobHouseGridDisplay mGridDisplay;
        TextButton mDoneButton;
        
        Gallery mGallery;
        
        int mMusicSeed;

        InventorySlotButton *mPackSlots[ NUM_PACK_SLOTS ];

        const char *mDoneButtonKey;
        
        char mDone;

        char *mDescription;
        
        char *mDeathMessage;
        
        void clearNotes();
    };

