#ifndef GALLERY_ARCHIVE_INCLUDED
#define GALLERY_ARCHIVE_INCLUDED


#include "PageComponent.h"

#include "SpriteButton.h"

#include "GallerySlotButton.h"



// fires actionPerformed when user clicks on selected object
class  GalleryArchive : public PageComponent, public ActionListener, 
                        public ActionListenerList {
        
    public:
        
        GalleryArchive( Font *inDisplayFont, double inX, double inY );
        

        virtual ~GalleryArchive();
        
        
        virtual void actionPerformed( GUIComponent *inTarget );
        
        virtual void clearObjects();
        
        virtual void addObject( int inObjectID );
        
        // returns ID of selected object
        virtual int swapSelectedObject( int inReplacementObjectID );
        
        
        // "#" on empty
        // destroyed by caller
        virtual char *getContentsString();

        
        // auto-hides itself when empty
        virtual char isVisible();
        
        
    protected:
        

        SimpleVector<int> mObjectList;
        
        int mSelectedIndex;
        
        GallerySlotButton mSlot;
        
        SpriteButton mUpButton;
        SpriteButton mDownButton;


        void triggerToolTip();

    };




#endif
