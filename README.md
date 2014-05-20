# PDF Fieldtype/Inputfield

Module for ProcessWire allowing you to easily generate thumbnails of the PDF files embedded to the site.

## Installation

Place the files in /site/modules/FieldtypePDF and install the FieldtypePDF module.  
[How to install or uninstall modules](http://modules.processwire.com/install-uninstall/).

## How to use

### In site's administration

Add a field and set its type to `PDF`.
Use the field the same way as the file field (obviously, this accepts only \*.pdf files).
After the file is uploaded you will see a small thumbnail of it.

### In templates

The PDF field type extends file field and adds new method to generate the thumbnail image of the PDF with the size of *width* x *height* in pixels.
```php
$page->pdfFile->thumbnail(width, height)
```
If you set one of the dimensions to `0` it will be computed automaticaly preserving the aspect ratio.

The generated image is saved in page's assets, so it will be **created only once**. The thumbnail is the instance of `PageImage`, so you can do with it whatever you can do with image fields. When you delete the PDF file the thumbnails are deleted too.
