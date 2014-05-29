<?php

/**
 * ProcessWire PagePDF
 *
 * Represents a single PDF file item attached to a page, typically via a FieldtypePDF field.
 * 
 * ProcessWire 2.x 
 * Copyright (C) 2013 by Ryan Cramer 
 * Licensed under GNU/GPL v2, see LICENSE.TXT
 * 
 * http://processwire.com
 *
 */

class PagePDF extends Pagefile {

    private $thumbnails = null;

    public function thumbnail($width, $height = 0) {
		$basename = basename($this->basename(), "." . $this->ext()); 		// i.e. myfile
		$basename .= '.' . $width . 'x' . $height . ".png";	// i.e. myfile.100x100.jpg or myfile.100x100nw.jpg
		$filename = $this->pagefiles->path() . $basename; 

		if(! is_file($filename)) {
            $imagick = new Imagick();
            $imagick->setResolution(300, 300);
            $imagick->setOption('pdf:use-cropbox', 'true');
            $imagick->setColorspace(Imagick::COLORSPACE_RGB);
            $imagick->readimage($this->filename . "[0]");
            $imagick->setImageFormat('png');
            $imagick->scaleImage($width, $height);
            $imagick->writeImage($filename);
		}

        $images = new Pageimages($this->pagefiles->getPage());
        $images->add(basename($filename));
        return $images->first();
    }

    public function removeThumbnails() {
		if(! is_null($this->thumbnails)) return $this->thumbnails; 

		$thumbnails = new Pageimages($this->pagefiles->page); 
		$dir = new DirectoryIterator($this->pagefiles->path); 

		foreach($dir as $file) {
			if($file->isDir() || $file->isDot()) continue; 			
			if(!$this->isThumbnail($file->getFilename())) continue; 
			unlink($file->getPathname()); 
		}
    }

	public function isThumbnail($basename) {
		$thumbnailName = basename($basename);
		$originalName = basename($this->basename, "." . $this->ext());  // excludes extension

		$re = 	'/^'  . 
			$originalName . '\.' .		// myfile. 
			'(\d+)x(\d+)' .			// 50x50	
			'\.png' .
			'$/';

		// if regex does not match, return false
		if(!preg_match($re, $thumbnailName, $matches)) return false;

        return true;
    }


	/**
	 * Delete the physical file on disk, associated with this PagePDF
	 *
	 */
	public function unlink() {
        @$this->removeThumbnails();
        return parent::unlink();
	}

	/**
	 * Rename this file to $basename
	 *
 	 * @param string $basename
	 * @return string|bool Returns basename on success, or boolean false if rename failed
	 *
	 */
	public function rename($basename) {
		$basename = $this->pagefiles->cleanBasename($basename, true); 
		if(rename($this->filename, $this->pagefiles->path . $basename)) {
            @$this->removeThumbnails();
			$this->set('basename', $basename); 
			return $basename; 
		}
		return false; 
	}

	/**
	 * Implement the hook that is called when a property changes (from Wire)
	 *
	 * Alert the $pagefiles of the change 
	 *
	 */
	public function ___changed($what) {
		if($what == 'file') {
            @$this->removeThumbnails();
		}

		parent::___changed($what); 
	}
}

