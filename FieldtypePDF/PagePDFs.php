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

use ProcessWire\Pagefiles;

/**
 * PagePDFs are a collection of PagePDF objects.
 *
 * Typically a PagePDFs object will be associated with a specific field attached to a Page.
 * There may be multiple instances of PagePDFs attached to a given Page (depending on what fields are in it's fieldgroup).
 */
class PagePDFs extends Pagefiles
{
	public function isValidItem($item)
	{
		return $item instanceof PagePDF;
	}

	public function makeBlankItem()
	{
		return new PagePDF($this, '');
	}

	public function add($item)
	{
		if(is_string($item)) {
			$item = new PagePDF($this, $item);
		}

		return parent::add($item);
	}
}
