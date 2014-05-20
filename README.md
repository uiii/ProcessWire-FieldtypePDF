PDF Inputfield/Fieldtype for ProcessWire
========================================

This module allowes you to easily generate thumbnails of the PDF files embedded to the site.

Installation
============

Place the files in /site/modules/InputfieldPDF and install the InputfieldPDF module.
[How to install or uninstall modules](http://modules.processwire.com/install-uninstall/). 

How to use
==========

**In site's administration** add a field and set its type to PDF.
Use the field the same way as the file field (obviously, this accepts only \*.pdf files).
After the file is uploaded you will see a small thumbnail of it.

**In templates**, the PDF field type has new method `$page->pdfFile->thumbnail(width, height)` which generates the thumbnail image of the PDF with the size of *width* x *height* in pixels. If you set one of the dimension to *0* it will be set automaticaly preserving the aspect ratio.

The generated image is the instance of `PageImage` and is saved in page's assets, so it will be created only once. When you delete the PDF file the thumbnails are deleted too.

