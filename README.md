# PDF Fieldtype/Inputfield 1.1.0

Module for ProcessWire CMS allowing you to easily generate images from the PDF files embedded to the site.

1. [Requirements](#requirements)
2. [Installation](#installation)
3. [How to use](#how-to-use)
	- [In site's administration](#in-sites-administration)
	- [In templates](#in-templates)
4. [API documentation](#api-documentation)
5. [Tests](#tests)
6. [Notes](#notes)
7. [Upgrading from 1.0.1 and lower](#upgrading-from-101-and-lower)
8. [Changelog](#changelog)

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

![Field settings](http://i.imgbox.com/xP1dt37q)

### In templates

The PDF field extends file field and adds new hookable [`___toImage($page = 0, $options = array())`](http://uiii.github.io/ProcessWire-FieldtypePDF/class-FieldtypePDF.PagePDF.html#____toImage) method to generate the image from PDF.

```php
$image = $page->pdfFile->toImage();
$image->size(100, 100);
```
Method accepts two optional parameters. First is the `$page`, which specifies the PDF's page number the image is generated from, default is 0. The exception is thrown if the page is out of range.

The second is `$options` parameter, which is an array of options to override the options set in administration.
```php
$options = array(
  'suffix' => array('suffix1', 'suffix2'), // suffixes used in filename
  'forceNew' => false, // if TRUE the image is regenerated if already exists
  'format' => 'JPEG', // image format
  'extension' => 'jpg', // image file extension
  'background' => '#FFFFFF', // background color used when the PDF has transparent background
  'resolution' => '300x300', // resolution used when reading the PDF
  'colorspace' => Imagick::COLORSPACE_RGB, // colorspace used when reading the PDF
  'imagickOptions' => array( // ImageMagick options
    'pdf:use-cropbox=true'
  ),
)
```

For each combinations of *page* and *suffixes* there will be one image. The generated images are saved in page's assets and will be **created only once** until *forceNew* options is TRUE. The image is the instance of `Pageimage`, so you can do with it whatever you can do with the image field. When you delete the PDF file the generated images are deleted too.

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

## Upgrading from 1.0.1 and lower

In 1.1.0 some methods of class PagePDF are deprecated. See the list [here](http://uiii.github.io/ProcessWire-FieldtypePDF/deprecated.html). You doesn't have to make any changes but it is recommended to use the new API, for compatibility with later versions.

Instructions for replacing the deprecated methods:

- `$page->pdf->thumbnail($width, $height)` replace with the code

```php
$image = $page->pdf->toImage();
$image->size($widht, $height);
```

- `isThumbnail($basename)` replace with `isImageOfThis($basename)`

> NOTE: There is certain incompatibility between these two methods. While `isThumbnail` returns TRUE for all the images generated from the PDF and also theirs derivatives (e.g. *pdf.jpg*, *pdf.100x100.jpg*), the `isImageOfThis` return TRUE only for the images generated directly from PDF (e.g. *pdf.jpg*). That doesn't change much, because you can use it in combination with `Pageimage::isVariation`.

- `removeThumbnails` replace with `removeImages`

## Changelog

### 1.1.0

- API change: New method `toImage`. Previous `thumbnail` and related methods are marked as deprecated
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
