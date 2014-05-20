<?php

/**
 * ProcessWire PagePDFs
 *
 * PagePDFs are a collection of PagePDF objects.
 *
 * Typically a PagePDFs object will be associated with a specific field attached to a Page. 
 * There may be multiple instances of PagePDFs attached to a given Page (depending on what fields are in it's fieldgroup).
 * 
 * ProcessWire 2.x 
 * Copyright (C) 2013 by Ryan Cramer 
 * Licensed under GNU/GPL v2, see LICENSE.TXT
 * 
 * http://processwire.com
 *
 */

class PagePDFs extends Pagefiles {

	/**
	 * Per the WireArray interface, items must be of type PagePDF
	 *
	 */
	public function isValidItem($item) {
		return $item instanceof PagePDF;
	}

	/**
	 * Per the WireArray interface, return a blank PagePDF
	 *
	 */
	public function makeBlankItem() {
		return new PagePDF($this, ''); 
	}

	/**
	 * Add a new PagePDF item, or create one from it's filename and add it.
	 *
	 * @param PagePDF|string $item If item is a string (filename) then the PagePDF instance will be created automatically.
	 * @return $this
	 *
	 */
	public function add($item) {

		if(is_string($item)) {
			$item = new PagePDF($this, $item); 
		}

		return parent::add($item); 
	}
}
