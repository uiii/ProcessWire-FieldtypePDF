# PDF Fieldtype/Inputfield 1.1.0

Module for ProcessWire CMS allowing you to easily generate images from the PDF files embedded to the site.

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
After the file is uploaded you will see a small thumbnail of it, just like for image field.

Image generation is highly configurable (image format, extension, background, ...). See the *PDF to image converter* section on field's *Details* tab.

![Field settings](http://i.imgbox.com/ssLdyPor.png)

### In templates

The PDF field extends file field and adds new hookable [`___toImage($page = 0, $options = array())`](http://uiii.github.io/ProcessWire-FieldtypePDF/class-FieldtypePDF.PagePDF.html#____toImage) method to generate the image from PDF.

```php
$image = $page->pdfFile->toImage();
$image->size(100, 100);
```
Method accepts two optional parameters. First is the `$page`, which specifies the PDF's page number the image is generated from, default is 0. The exception is thrown if the page is out of range.

The second is `$options` parameter, which is the array of options to override the options set in administration.
```php
$options = array(
  'suffix' => array('suffix1', 'suffix2'), // suffixes used in filename
  'forceNew' => false, // if TRUE the image is regenerated if already exists
  'format' => 'JPEG', // image format
  'extension' => 'jpg', // image file extension
  'background' => '#FFFFFF', // background color used when the PDF has transparent background, 
  'resolution' => '300x300', // resolution used when reading the PDF
  'colorspace' => Imagick::COLORSPACE_RGB, // colorspace used when reading the PDF
  'imagickOptions' => array( // ImageMagick options
    'pdf:use-cropbox=true'
  ),
)
```

For each combinations of *page* and *suffixes* there will be one image. The generated images are saved in page's assets and will be **created only once** until *forceNew* options is TRUE. The image is the instance of `Pageimage`, so you can do with it whatever you can do with image field. When you delete the PDF file the generated images are deleted too.

## API documentation

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
