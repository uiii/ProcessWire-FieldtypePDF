<?php

class PagePDFs extends Pagefiles {

	public function isValidItem($item) {
		return $item instanceof PagePDF;
	}

	public function makeBlankItem() {
		return new PagePDF($this, ''); 
	}

	public function add($item) {

		if(is_string($item)) {
			$item = new PagePDF($this, $item); 
		}

		return parent::add($item); 
	}
}
