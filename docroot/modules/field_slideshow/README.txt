--- README  -------------------------------------------------------------

Field Slideshow

Provides a Slideshow format for displaying Image fields,
using the JQuery Cycle plugin.

Compared to Views slideshows, building the slideshow from multiple nodes,
this module builds it from a single node, if you're using a multi-valued
Image field.

--- INSTALLATION --------------------------------------------------------

1 - Extract the module into /modules directory
2 - Download the JQuery Cycle plugin here :
    http://jquery.malsup.com/cycle/download.html
    (don't choose the Lite version), and move the downloaded
    jquery.cycle.all.js file into /sites/all/libraries/jquery.cycle/

--- USAGE ---------------------------------------------------------------

1 - Enable Field Slideshow at /admin/modules.
2 - Create or edit a content type at /admin/structure/types and
    include an Image field.
3 - Edit this image field, so that multiple image files may be added
    ("Number of values" setting at admin/structure/types/manage/
    {content type}/fields/{field_image}).
4 - Go to "Manage display" for your content type
    (/admin/structure/types/manage/{content type}/display) and
    switch the format of your multiple image field from Image to Slideshow.
5 - Click the settings wheel in the slideshow-formatted multiple
    image field to edit advanced settings.
6 - Save! and here you go.

--- Slideshow with fieldcollection -----------------------------------------

1 - Enable Field Collection at admin/modules.
2 - Create or edit a content type at /admin/structure/types and
    add or edit field Collection field which has an image field.
    Select the format as "Slideshow" and click the settings wheel
    and set the desired options.
3 - Choose the field collection image field in the "Image Field" list.
4 - Assign the appropriate settings for your slideshow.
5 - Update and save the settings.
    Here you for slideshow with field collection !!

--- KNOWN ISSUES --------------------------------------------------------

1 - Media and Image crop not supported.
2 - Colorbox works for Default view mode only.

--- FUTURE ENCHANCEMENTS ------------------------------------------------

1 - Slideshow Jcarousel will be added in future.

--- AVAILABLE OPTIONS ---------------------------------------------------

Image styles
Caption
Link
Transition effect
Transition speed
Timeout
Order
Pager (numbers, thumbnails)
Prev/Next controls
Pause on hover

Integration with Colorbox.

Written by Deepika
