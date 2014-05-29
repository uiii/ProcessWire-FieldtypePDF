<?php

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


	public function unlink() {
        @$this->removeThumbnails();
        return parent::unlink();
	}

	public function rename($basename) {
		$basename = $this->pagefiles->cleanBasename($basename, true); 
		if(rename($this->filename, $this->pagefiles->path . $basename)) {
            @$this->removeThumbnails();
			$this->set('basename', $basename); 
			return $basename; 
		}
		return false; 
	}

	public function ___changed($what) {
		if($what == 'file') {
            @$this->removeThumbnails();
		}

		parent::___changed($what); 
	}
}

