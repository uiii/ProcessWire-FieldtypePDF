# PDF Fieldtype/Inputfield 1.1.2

Module for [ProcessWire CMS](https://processwire.com) allowing you to easily generate images from the PDF files embedded to the site.

1. [Requirements](#requirements)
2. [Installation](#installation)
3. [How to use](#how-to-use)
	- [In site's administration](#in-sites-administration)
	- [In templates](#in-templates)
4. [API documentation](#api-documentation)
5. [Tests](#tests)
6. [Upgrading from 1.0.1 and lower](#upgrading-from-101-and-lower)
7. [Troubleshooting](#troubleshooting)
8. [Changelog](#changelog)

## Requirements

- Processwire 2.5+
- ImageMagick PHP extension
- Ghostscript

## Installation

[How to install or uninstall modules](http://modules.processwire.com/install-uninstall/).

### Via Composer
In your ProcessWire installation root run:
```
composer require uiii/processwire-fieldtypepdf
```
Login to your ProcessWire admin and go to *Modules* > *Refresh* and install the module.

If you want to read more about ProcessWire and Composer visit [https://processwire.com/blog/posts/composer-google-calendars-and-processwire/](https://processwire.com/blog/posts/composer-google-calendars-and-processwire/)

## How to use

### In site's administration

Add a field and set its type to `PDF`.
Use the field the same way as the file field (obviously, this accepts only \*.pdf files).
After the file is uploaded you will see a small thumbnail of it, just like for image field.

Image generation is highly configurable (image format, extension, background, ...). See the *PDF to image converter* section on field's *Details* tab.

![Field settings](http://i.imgbox.com/xP1dt37q)

### In templates

> There are some backward-compatible API changes against the version 1.0.1 and lower, see [Upgrading from 1.0.1 and lower](#upgrading-from-101-and-lower).

The PDF field extends file field and adds new hookable [`___toImage($page = 0, $options = array())`](http://uiii.github.io/ProcessWire-FieldtypePDF/dev/class-FieldtypePDF.PagePDF.html#____toImage) method to generate the image from PDF.

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
	)
)
```

For each combinations of *page* and *suffixes* there will be one image. The generated images are saved in page's assets and will be **created only once** until *forceNew* options is TRUE. The image is the instance of `Pageimage`, so you can do with it whatever you can do with the image field. When you delete the PDF file the generated images are deleted too.

## API documentation

Generate into *doc* directory:
```
apigen generate -d doc
```

## Tests

> **DO NOT** run the tests against the production site. They modify the fields, templates and pages as they need, so can potentially damage your site!

Prepare the PW testing installation and export the `PW_PATH` environment variable containing the path to the root of that installation. Copy the module sources in the `$PW_PATH/site/modules/FieldtypePDF` directory.

Install required packages:
```
composer install
```

Run the tests
```
./vendor/bin/phpunit
```

### Test multiple ProcessWire versions (automatically)

You can also automatically test against multiple ProcessWire versions.
It uses [PW-Test](https://github.com/uiii/pw-test) tool for it.

1. Install reuquired packages:

	```
	composer install
	```

2. Create a config file:

	```
	cp pw-test.json.example pw-test.json
	```

3. Edit `pw-test.json` file and fill the values

> **WARNING**: The tool creates and drops a database
> for each ProcessWire installation, so configure
> the `db` connection parameters carefully.

4. Run the tests:

	```
	vendor/bin/pw-test
	```

## Upgrading from 1.0.1 and lower

In 1.1.0 some methods of class PagePDF are deprecated. See the list [here](http://uiii.github.io/ProcessWire-FieldtypePDF/dev/deprecated.html). You doesn't have to make any changes but it is recommended to use the new API, for compatibility with later versions.

Instructions for replacing the deprecated methods:

- `$page->pdf->thumbnail($width, $height)` replace with the code

```php
$image = $page->pdf->toImage();
$image->size($width, $height);
```

- `isThumbnail($basename)` replace with `isImageOfThis($basename)`

> NOTE: There is certain incompatibility between these two methods. While `isThumbnail` returns TRUE for all the images generated from the PDF and also theirs derivatives (e.g. *pdf.jpg*, *pdf.100x100.jpg*), the `isImageOfThis` return TRUE only for the images generated directly from PDF (e.g. *pdf.jpg*). That doesn't change much, because you can use it in combination with `Pageimage::isVariation`.

- `removeThumbnails` replace with `removeImages`

## Troubleshooting

### Thumbnail's colors do not match the colors in PDF

To fix that, you need to made some changes in ImageMagick delegate files. Detailed instructions can be found here: http://www.lassosoft.com/CMYK-Colour-Matching-with-ImageMagick

### GhostScript exceptions occured

If you got some *GhostScript* exceptions when generating image, update *GhostScript* and *ImageMagick* to the latest versions.

If you can't, you can use the **fallback mode**. Turn it on in the module's settings.
> Be aware of that this will produce low quality images and most of the field type options won't be abvailable.

## Changelog

### 1.1.2

- Added ProcessWire 3.x support
- Module is installable via Composer
- Use [PW-Test](https://github.com/uiii/pw-test) for testing against multiple versions of ProcessWire

### 1.1.1

- Fix module's installation by classname

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
