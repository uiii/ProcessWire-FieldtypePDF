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

use DirectoryIterator;
use Exception;

use ProcessWire\Pagefile;
use ProcessWire\Pageimage;
use ProcessWire\Pageimages;

/**
 * Represents a single PDF file item attached to a page, typically via a FieldtypePDF field.
 */
class PagePDF extends Pagefile
{
	public static $defaultImageExtension = 'jpg';

	protected $options = array();

	protected $images;

	/**
	 * Construct a new PagePDF
	 *
	 * @param PagePDFs $pagefiles Owning collection
	 * @param string $filename Full path and filename to this pagefile
	 */
	public function __construct(PagePDFs $pagefiles, $filename)
	{
		parent::__construct($pagefiles, $filename);

		$field = $pagefiles->getField();
		foreach($field->getArray() as $key => $value) {
			if (preg_match('/^converter(.+)$/', $key, $matches)) {
				$this->options[lcfirst($matches[1])] = $value;
			}
		}

		if ($field->get('imageExtension')) {
			$this->options['extension'] = $field->get('imageExtension');
		}

		if ($field->type->fallbackMode) {
			$this->options['fallbackMode'] = true;
		}

		$this->images = new Pageimages($this->pagefiles->getPage());
	}

	/**
	 * Convert PDF to image
	 *
	 * This generates one image for each combinations of $page and $options['suffix'].
	 * If the file already exists it isn't regenerated until $options['forceNew'] is TRUE.
	 *
	 * @param int $page Number of PDF's page (indexed from 0) to be converted to image
	 * @param type $options
	 *
	 * Available options are:
	 *
	 * - sufix (string[]) - Suffixes to be used in image's filename
	 * - forceNew (boolean) - Whether to overwrite the image if already exists
	 *
	 * Accepts also converter options, see {@link FieldtypePDF\PDFConverter::setOptions}.
	 *
	 * @return Pageimage
	 */
	public function ___toImage($page = 0, $options = array())
	{
		if (is_array($page)) {
			$options = $page;
			$page = 0;
		}

		$defaultOptions = array(
			'extension' => self::$defaultImageExtension,
			'suffix' => array(),
			'forceNew' => false,
		);

		if ($page > 0) {
			$defaultOptions['suffix'][] = 'page' . $page;
		}

		$options = array_replace($defaultOptions, $this->options, $options);

		$suffixStr = '';
		if(!empty($options['suffix'])) {
			$suffix = is_array($options['suffix']) ? $options['suffix'] : array($options['suffix']);
			sort($suffix);
			foreach($suffix as $key => $s) {
				$s = strtolower($this->wire('sanitizer')->fieldName($s));
				if(empty($s)) unset($suffix[$key]);
					else $suffix[$key] = $s;
			}
			if(count($suffix)) $suffixStr = '-' . implode('-', $suffix);
		}

		// e.g. myfile.pdf -> myfile-page2.jpg
		$basename = sprintf('%s%s.%s',
			basename($this->basename(), "." . $this->ext()),
			$suffixStr,
			$options['extension']
		);

		$filename = $this->pagefiles->path() . $basename;
		$exists = file_exists($filename);

		if(! $exists || $options['forceNew']) {
			if($exists && $options['forceNew']) {
				$image = new Pageimage($this->images, $filename);
				$image->unlink();
			}

			try {
				$converter = new PDFConverter($this->filename, $options);
				$converter->toImage($page, $filename);

				if($this->config->chmodFile) {
					chmod($filename, octdec($this->config->chmodFile));
				}
			} catch(Exception $e) {
				if ($this->pagefiles->getPage()->template === 'admin') {
					$this->error($e->getMessage());
					$this->error("PDF to image conversion failed for $filename");
				} else {
					throw $e;
				}
			}
		}

		$image = new Pageimage($this->images, $filename);
		$this->images->add($image);
		return $image;
	}

	/**
	 * Test whether $basename is image generated from this PDF
	 *
	 * @param type $basename
	 * @return boolean
	 */
	public function isImageOfThis($basename)
	{
		$imageName = basename($basename);
		$originalName = basename($this->basename, "." . $this->ext());  // excludes extension

		$re = '/^'
			. $originalName // myfile
			. '(?:-([-_a-zA-Z0-9]+))?' // -suffix1 or -suffix1-suffix2, etc.
			. '\.[^.]+' // .jpg
			. '$/';

		// if regex does not match or file is PDF, return false
		if(! preg_match($re, $imageName) || preg_match('/^.*pdf$/', $imageName)) {
			return false;
		}

		return true;
	}

	/**
	 * Get all images generated from this PDF
	 *
	 * @return Pageimages
	 */
	public function getImages()
	{
		$images = new Pageimages($this->pagefiles->page);
		$dir = new DirectoryIterator($this->pagefiles->path);

		foreach($dir as $file) {
			if($file->isDir() || $file->isDot()) continue;
			if(! $this->isImageOfThis($file->getFilename())) continue;
			$images->add($file->getFilename());
		}

		return $images;
	}

	/**
	 * Remove all generated images.
	 *
	 * @return void
	 */
	public function removeImages()
	{
		$images = $this->getImages();

		foreach($images as $image) {
			$image->unlink();
		}

		return $this;
	}

	/**
	 * Delete the physical file on disk associated with this PDF.
	 *
	 * Unlinks also all generated images.
	 *
	 * @return bool
	 */
	public function unlink()
	{
		$this->removeImages();
		return parent::unlink();
	}

	/**
	 * @deprecated since version 1.1.0, please use toImage() instead
	 * @param int $width
	 * @param int $height
	 * @return Pageimage
	 */
	public function thumbnail($width, $height = 0)
	{
		$height = $height ?: $width;

		$image = $this->toImage();
		return $image->size($width, $height);
	}

	/**
	 * @deprecated since version 1.1.0, please use isImageOfThis() instead
	 * @param string $basename
	 */
	public function isThumbnail($basename)
	{
		$images = $this->getImages();

		if ($images->count() == 0) {
			$images->add($this->toImage());
		}

		foreach($images as $image) {
			if($image->basename === $basename || $image->isVariation($basename)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @deprecated since version 1.1.0, please use removeImages() instead
	 */
	public function removeThumbnails()
	{
		$this->removeImages();
	}
}
