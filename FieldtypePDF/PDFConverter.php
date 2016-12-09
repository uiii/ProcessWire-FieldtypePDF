<?php

/*
 * The MIT License
 *
 * Copyright 2016 Richard Jedlička <jedlicka.r@gmail.com> (http://uiii.cz)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace FieldtypePDF;

use Imagick;

/**
 * PDF to image converter
 */
class PDFConverter
{
	/**
	 * @var array
	 */
	public static $defaultOptions = array(
		'format' => 'JPEG',
		'extension' => 'jpg',
		'background' => '#FFFFFF',
		'resolution' => '300x300',
		'colorspace' => Imagick::COLORSPACE_RGB,
		'imagickOptions' => array(
			'pdf:use-cropbox=true'
		),
		'fallbackMode' => false
	);

	/**
	 * @var array
	 */
	protected $options;

	/**
	 *
	 * @var string
	 */
	protected $pdfFilename;

	/**
	 * Check if colorspace is supported by ImageMagick.
	 *
	 * @return bool
	 */
	public static function isColorspaceSupported()
	{
		return method_exists('Imagick', 'setColorspace');
	}

	/**
	 * Constructor
	 *
	 * @param string $pdfFilename PDF file to be converted
	 * @param array $options Converter options (Optional)
	 * @see setOptions()
	 */
	public function __construct($pdfFilename, $options = array())
	{
		$this->pdfFilename = $pdfFilename;
		$this->setOptions($options);
	}

	/**
	 * Set converter options
	 *
	 * @param array $options
	 *
	 * Available options are:
	 *
	 * - format (string) - format of the image
	 * - extension (string) - image file extension
	 * - background (string) - image background (used when the PDF's background is transparent),
	 * 	to leave the background transparent set NULL
	 * - resolution (string) - resolution used when reading the PDF (e.g. '300x300')
	 * - colorspace (int) - colorspace used when reading the PDF (Imagick::COLORSPACE_* constant)
	 * - imagickOptions (string[]) - ImageMagick options (each option in format 'key=value')
	 * - fallbackMode (bool) - Fallback mode (produces low quality images, but may work where normal mode don't)
	 *
	 * For converter default options see {@link $defaultOptions}
	 */
	public function setOptions(array $options)
	{
		$options = array_replace(self::$defaultOptions, $options);

		if (isset($options['resolution']) && $options['resolution']) {
			$resolution = $options['resolution'];

			if (is_string($resolution)) {
				$resolution = explode('x', $options['resolution']);
			} elseif (is_numeric($resolution)) {
				$resolution = array($resolution);
			}

			if (is_array($resolution)) {
				// append the resolution's second dimension if not set
				if (count($resolution) === 1) {
					$resolution[] = $resolution[0];
				}

				$options['resolution'] = $resolution;
			}
		}

		if (isset($options['imagickOptions']) && ! is_array($options['imagickOptions'])) {
			$options['imagickOptions'] = array($options['imagickOptions']);
		}

		$this->options = $options;
	}

	/**
	 * Get converter options
	 *
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * Convert PDF to image specified by filename.
	 *
	 * @param int $page Number of PDF's page to be converted to image (indexed from 0)
	 * @param string $imageFilename Filename of output image
	 */
	public function toImage($page, $imageFilename)
	{
		$options = $this->options;

		$imagick = new Imagick();
		$backgroundImagick = new Imagick();

		if ($options['fallbackMode']) {
			$imagick->clear();
			$imagick = new Imagick(sprintf('%s[%s]', $this->pdfFilename, $page));
		} else {
			if ($options['resolution']) {
				$resolution = $options['resolution'];
				$imagick->setResolution($resolution[0], $resolution[1]);
				$backgroundImagick->setResolution($resolution[0], $resolution[1]);
			}

			foreach($options['imagickOptions'] as $defition) {
				$defition = explode('=', $defition);
				$imagick->setOption($defition[0], $defition[1]);
			}

			if ($options['colorspace'] && self::isColorspaceSupported()) {
				$imagick->setColorspace($options['colorspace']);
			}

			$imagick->readimage(sprintf('%s[%s]', $this->pdfFilename, $page));
		}

		$image = $imagick;

		if ($options['background'] !== 'transparent') {
			$backgroundImagick->newImage(
				$imagick->getimagewidth(),
				$imagick->getimageheight(),
				$options['fallbackMode'] ? self::$defaultOptions['background'] : $options['background']
			);

			$backgroundImagick->compositeimage($imagick, Imagick::COMPOSITE_OVER, 0, 0);
			$image = $backgroundImagick;
		}

		$image->writeImage(sprintf('%s:%s', $options['format'], $imageFilename));

		$backgroundImagick->clear();
		$imagick->clear();
	}
}
