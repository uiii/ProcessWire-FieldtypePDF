# PDF Fieldtype/Inputfield

Module for ProcessWire allowing you to easily generate thumbnails of the PDF files embedded to the site.

## Requirements

- Processwire 2.4+
- ImageMagick PHP extension
- Ghostscript

## Installation

[How to install or uninstall modules](http://modules.processwire.com/install-uninstall/).

## How to use

### In site's administration

Add a field and set its type to `PDF`.
Use the field the same way as the file field (obviously, this accepts only \*.pdf files).
After the file is uploaded you will see a small thumbnail of it.

Image generation is highly configurable (image format, extension, background, ...). See the *PDF to image converter* section on field's *Details* tab.

### In templates

The PDF field type extends file field and adds new method to generate the image from PDF.
```php
$image = $page->pdfFile->toImage();
$image->size(100, 100);
```
You can also specify PDF's page number as the first parameter of `toImage($page)`, default is 0. The generated image is saved in page's assets, so it will be **created only once**. The image is the instance of `Pageimage`, so you can do with it whatever you can do with image fields. When you delete the PDF file the generated images are deleted too.

## API documentation

TODO
See http://uiii.github.io/ProcessWire-FieldtypePDF. 

Or generate your own into *doc* directory:
```
apigen generate -d doc
```

## Tests

TODO

## Notes

In some cases, the thumbnail's colors might not match the colors in PDF. To fix that, you need to made some changes in ImageMagick delegate files.

Detailed instructions can be found here: http://www.lassosoft.com/CMYK-Colour-Matching-with-ImageMagick

## Changelog

### 1.1.0

- API change: New method `toImage`. Previous `thumbnail` and related methods are marked as **deprecated**
- PDF to image converter is now configurable in admin [issue [#7](https://github.com/uiii/ProcessWire-FieldtypePDF/issues/7)]
- You can specify which page of the PDF's the image is generated from [issue [#3](https://github.com/uiii/ProcessWire-FieldtypePDF/issues/3)]
- Fix bugs [issue [#4](https://github.com/uiii/ProcessWire-FieldtypePDF/issues/4), [#6](https://github.com/uiii/ProcessWire-FieldtypePDF/issues/6)]
- Add ApiGen config for API documentation generation
- Add PHPUnit tests
- Add license (MIT)

### 1.0.1

- Set important ImageMagick settings before conversion  [issue [#1](https://github.com/uiii/ProcessWire-FieldtypePDF/issues/1)]
- Added module requirements check [issue [#2](https://github.com/uiii/ProcessWire-FieldtypePDF/issues/2)]
- Updated README
