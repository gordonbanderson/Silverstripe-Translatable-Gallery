##Translatable Gallery
I wanted a gallery that is both translatable and had a bulk upload facility.  Due to limitations with DataObjects I've modelled the Photograph on a Page.  Using the Uploadify module it is now possible to bulk upload.

## Usage
Install DataObjectManager and Uploadify as prerequisites.

Move this repository to your root folder and then go to the standard /dev/build page.  You can now add a Gallery when using Create mode, and this gallery has a bulk upload facility.

## Known Bugs
The left hand menu currently does not refresh after bulk upload.  The work around is to refresh the browser page (/admin/cms)

## Caveats
Not tested thoroughly so probably best avoid on production sites for the moment :)
