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

namespace ProcessWire;

use FieldtypePDF\PagePDF;

class InputfieldPDF extends InputfieldFile implements InputfieldItemList
{
	public static function getModuleInfo()
	{
		return array(
			'version' => 201,
			'title' => __('PDF files with thumbnails', __FILE__), // Module Title
			'summary' => __('One or more PDF files upload with thumbnails', __FILE__), // Module Summary
			'href' => 'http://modules.processwire.com/modules/fieldtype-pdf',
			'author' => "Richard Jedlička",
			'requires' => array(
				'ProcessWire>=3.0.0',
				'FieldtypePDF'
			)
		);
	}

	public function init()
	{
		parent::init();
		$this->set('extensions', 'PDF');
		$this->set('adminThumbs', false);

		$options = $this->wire('config')->adminThumbOptions;
		if(!is_array($options)) $options = array();
		if(empty($options['width']) && empty($options['height'])) $options['height'] = 100; // default
		$this->set('adminThumbWidth', empty($options['width']) ? 0 : (int) $options['width']);
		$this->set('adminThumbHeight', empty($options['height']) ? 0 : (int) $options['height']);
		$this->set('adminThumbScale', empty($options['scale']) ? 1.0 : (float) $options['scale']);
	}

	public function getAdminThumb(PagePDF $pdf)
	{
		$thumb = $pdf->toImage();
		$thumbInfo = array();

		/** @var InputfieldImage */
		$inputfieldImage = $this->modules->get('InputfieldImage');
		if (method_exists($inputfieldImage, 'getAdminThumb')) { // PW 2.6+
			$inputfieldImage->set('adminThumbs', true);
			return $inputfieldImage->getAdminThumb($pdf->toImage());
		} else {
			$error = '';
			$thumbAttrs = array();

			if($this->adminThumbs) {
				$thumbHeight = $thumb->height;
				if($thumbHeight > $this->adminThumbHeight) {
					// create a variation for display with this inputfield
					$thumb = $thumb->height($this->adminThumbHeight);
					if($thumb->error) $error = "<span class='ui-state-error-text'>$thumb->error</span>";
					$thumbHeight = $this->adminThumbHeight;
				}
				$thumbAttrs['height'] = $thumbHeight;
				$thumbAttrs['width'] = $thumb->width;
			}

			$thumbAttrs['src'] = $thumb->url;

			// ensure cached image doesn't get shown when replacing same filename
			if($this->overwrite) $thumbAttrs['src'] .= "?m=" . filemtime($pdf->pathname);

			$thumbInfo['attr'] = $thumbAttrs;
			$thumbInfo['error'] = $error;

			$markup = "<img ";
			foreach($thumbAttrs as $key => $value) $markup .= "$key=\"$value\" ";
			$markup .= " />";

			$thumbInfo['markup'] = $markup;
		}

		$thumbInfo['thumb'] = $thumb;

		return $thumbInfo;
	}


	public function ___render() {
		$this->wire('modules')->loadModuleFileAssets('InputfieldFile');

		return parent::___render();
	}

	protected function ___renderItem($pagefile, $id, $n)
	{
		$thumb = $this->getAdminThumb($pagefile);
		$error = $thumb['error'] ? "<span class='ui-state-error-text'>" . $this->wire('sanitizer')->entities($thumb['error']) . "</span>" : "";

		$out = "\n\t\t<p class='InputfieldFileInfo InputfieldItemHeader ui-widget ui-widget-header'>";

		if (function_exists('wireIconMarkupFile')) { // PW 2.6+
			$out .= "\n\t\t\t" . wireIconMarkupFile($pagefile->basename, "fa-fw HideIfEmpty");
		} else {
			$out .= "\n\t\t\t<span class='ui-icon ui-icon-arrowthick-2-n-s HideIfSingle HideIfEmpty'></span>" .
				"\n\t\t\t<span class='ui-icon ui-icon-arrowthick-1-e HideIfMultiple'></span>";
		}

		$out .= "\n\t\t\t<a class='InputfieldFileName' target='_blank' href='{$pagefile->url}'>{$pagefile->basename}</a> " .
			"\n\t\t\t<span class='InputfieldFileStats'>" . str_replace(' ', '&nbsp;', $pagefile->filesizeStr) . "</span> " .
			"\n\t\t\t<label class='InputfieldFileDelete'>" .
				"<input type='checkbox' name='delete_$id' value='1' title='" . $this->_('Delete') . "' />" .
				"<i class='fa fa-fw fa-trash'></i></label>";

		$out .= "\n\t\t</p>"; // .InputfieldFileInfo.InputfieldItemHeader

		$out .= "\n\t\t<div class='InputfieldFileData ui-widget-content'>";

		if ($this->adminThumbs) {
			$out .= "\n\t\t\t<div class='InputfieldImagePreview'>" .
				"\n\t\t\t\t<a target='_blank' href='{$pagefile->url}'>$thumb[markup]</a>" .
				"\n\t\t\t</div>";
		}

		$out .= "\n\t\t\t" . $error . $this->renderItemDescriptionField($pagefile, $id, $n) .
			"\n\t\t\t<input class='InputfieldFileSort' type='text' name='sort_$id' value='$n' />";

		$out .= "\n\t\t</div>"; // . InputfieldFileData

		return $out;
	}

	public function ___getConfigInputfields()
	{
		$inputfields = parent::___getConfigInputfields();

		/** @var InputfieldCheckbox */
		$field = $this->modules->get('InputfieldCheckbox');
		$field->attr('name', 'adminThumbs');
		$field->attr('value', 1);
		$field->attr('checked', $this->adminThumbs ? 'checked' : '');
		$field->label = $this->_('Display thumbnails in page editor?');
		$inputfields->add($field);

		return $inputfields;
	}

	/**
	 * Specify which config inputfields can be used with template/field contexts
	 *
	 * PW 2.6+
	 *
	 * @param Field $field
	 * @return array
	 *
	 */
	public function ___getConfigAllowContext($field)
	{
		$fieldNames = method_exists(get_parent_class(), '___getConfigAllowContext')
			? parent::___getConfigAllowContext($field)
			: array();

		return array_merge($fieldNames, array(
			'adminThumbs'
		));
	}
}
