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
use FieldtypePDF\PagePDFs;
use FieldtypePDF\PDFConverter;

class FieldtypePDF extends FieldtypeFile implements ConfigurableModule
{
	protected static $defaults = array(
		'fallbackMode' => false
	);

	public static function getModuleInfo()
	{
		return array(
			'version' => 201,
			'title' => __('PDF with thumbnail', __FILE__),
			'summary' => __('Field that stores one or more PDF files allowing thumbnail creation.', __FILE__),
			'href' => 'http://modules.processwire.com/modules/fieldtype-pdf',
			'author' => 'Richard Jedlička',
			'installs' => 'InputfieldPDF',
			'autoload' => true,
			'requires' => array(
				'ProcessWire>=3.0.0'
			)
		);
	}

	public function init()
	{
		spl_autoload_register(function($classname) {
			$classname = ltrim($classname, '\\');
			$filename = sprintf('%s/%s.php', __DIR__, str_replace('\\', DIRECTORY_SEPARATOR, $classname));

			if (is_file($filename)) {
				require_once $filename;
			}
		});
	}

	public function ___install()
	{
		if(! class_exists('Imagick')) {
			throw new WireException(__('FieldtypePDF module requires the ImageMagick PHP extension.'));
		}
	}

	public function set($key, $value)
	{
		if($key === 'converterImagickOptions' && is_string($value)) {
			$value = explode("\n", $value);
		}

		return parent::set($key, $value);
	}

	public function getBlankValue(Page $page, Field $field)
	{
		$pagePDFs = new PagePDFs($page);
		$pagePDFs->setField($field);
		$pagePDFs->setTrackChanges(true);

		return $pagePDFs;
	}

	protected function getBlankPagefile(Pagefiles $pagefiles, $filename)
	{
		return new PagePDF($pagefiles, $filename);
	}

	protected function getDefaultFileExtensions()
	{
		return 'pdf';
	}

	public function ___getConfigInputfields(Field $field)
	{
		$inputfields = parent::___getConfigInputfields($field);

		// hide input extensions field
		$extensionsInputField = $inputfields->get('extensions');
		$extensionsInputField->collapsed = Inputfield::collapsedHidden;

		// add fields for thumbnail creation settings
		$converterOptions = PDFConverter::$defaultOptions;

		/** @var InputfieldFieldset */
		$thumbnailFieldset = $this->modules->get('InputfieldFieldset');
		$thumbnailFieldset->label = $this->_('PDF to image converter');
		$thumbnailFieldset->description = $this->_('Options used when creating images from PDF files.');

		/** @var InputfieldText */
		$formatField = $this->modules->get('InputfieldText');
		$formatField->attr('name', 'converterFormat');
		$formatField->attr('value', $field->converterFormat ?: $converterOptions['format']);
		$formatField->label = $this->_('Image format');
		$formatField->description = $this->_('Format used when creating the image (recomeneded are JPEG or PNG). Don\'t forget to check the file extension.');
		$url = 'http://www.imagemagick.org/script/formats.php#supported';
		$formatField->notes = $this->_("For supported formats see [$url]($url)");
		$formatField->required = true;
		$thumbnailFieldset->add($formatField);

		/** @var InputfieldText */
		$extensionField = $this->modules->get('InputfieldText');
		$extensionField->attr('name', 'imageExtenstion');
		$extensionField->attr('value', $field->imageExtension ?: PagePDF::$defaultImageExtension);
		$extensionField->label = $this->_('File extension');
		$extensionField->description = $this->_('Sould correspond the image format.');
		$extensionField->required = true;
		$thumbnailFieldset->add($extensionField);

		if (! $this->fallbackMode) {
			/** @var InputfieldText */
			$backgroundField = $this->modules->get('InputfieldText');
			$backgroundField->attr('name', 'converterBackground');
			$backgroundField->attr('value', $field->converterBackground ?: $converterOptions['background']);
			$backgroundField->label = $this->_('Image background');
			$backgroundField->description = $this->_('Color used as a background for transparent PDFs. Enter \'transparent\' if you don\'t want the background to be set. Default color is white.');
			$url = 'http://www.imagemagick.org/script/color.php';
			$backgroundField->notes = $this->_("For supported colors see [$url]($url)");
			$thumbnailFieldset->add($backgroundField);

			/** @var InputfieldFieldset */
			$imagickFieldset = $this->modules->get('InputfieldFieldset');
			$imagickFieldset->label = $this->_('ImageMagick settings (advanced)');
			$imagickFieldset->collapsed = Inputfield::collapsedYes;
			$imagickFieldset->description = $this->_('Settings set to the ImageMagick instance before reading the PDF file. **Change this only if you know what you are doing.**');

			/** @var InputfieldText */
			$resolutionField = $this->modules->get('InputfieldText');
			$resolutionField->attr('name', 'converterResolution');
			$resolutionField->attr('value', $field->converterResolution ?: $converterOptions['resolution']);
			$resolutionField->label = $this->_('density');
			$url = 'http://www.imagemagick.org/script/command-line-options.php#density';
			$resolutionField->notes = $this->_("see [$url]($url)");

			/** @var InputfieldSelect */
			$colorspaceField = $this->modules->get('InputfieldSelect');
			$colorspaceField->attr('name', 'converterColorspace');
			$colorspaceField->label = $this->_('Color space');
			$colorspaceField->description = $this->_('Leave empty if you don\'t want to set.');

			$imagickReflection = new \ReflectionClass('Imagick');
			foreach ($imagickReflection->getConstants() as $name => $value) {
				if (preg_match('/^COLORSPACE_([^_]+)$/', $name, $matches)) {
					$name = $matches[1];

					if ($name === 'UNDEFINED') {
						$colorspaceField->addOption($value, '');
					} else {
						$colorspaceField->addOption($value, $name);
					}
				}
			}

			$colorspaceField->attr('value', $field->converterColorspace === null ? $converterOptions['colorspace'] : $field->converterColorspace);

			if (! PDFConverter::isColorspaceSupported()) {
				$colorspaceField->attr('disabled', true);
				$colorspaceField->addOption(\Imagick::COLORSPACE_UNDEFINED, 'not supported');
				$colorspaceField->notes = $this->_('Supported since ImageMagick version 6.5.7');
			}

			/** @var InputfieldTextArea */
			$optionsField = $this->modules->get('InputfieldTextarea');
			$optionsField->attr('name', 'converterImagickOptions');
			$optionsField->attr('rows', 3);
			$optionsField->label = $this->_('Options');
			$optionsField->description = $this->_('One definition per line (key=value).');
			$url = 'http://www.imagemagick.org/script/command-line-options.php#define';
			$optionsField->notes = $this->_("See [$url]($url)");

			$optionsField->attr('value', $field->converterImagickOptions ?: implode("\n", $converterOptions['imagickOptions']));

			$imagickFieldset->append($resolutionField);
			$imagickFieldset->append($colorspaceField);
			$imagickFieldset->append($optionsField);

			$thumbnailFieldset->add($imagickFieldset);
		} else {
			$thumbnailFieldset->notes = $this->_(
				'**Fallback mode is ON:** This will produce low quality images and some field type options won\'t be available, but may work where normal mode doesn\'t.' .
				'You can turn it OFF [here](' . $this->config->urls->admin . 'module/edit?name=' . $this->name . ').'
			);
		}

		$inputfields->add($thumbnailFieldset);

		return $inputfields;
	}

	public static function getModuleConfigInputfields(array $data)
	{
		$data = array_merge(self::$defaults, $data);

		$modules = wire('modules');

		$inputfields = new InputfieldWrapper;

		$field = $modules->get('InputfieldCheckbox');
		$field->attr('name', 'fallbackMode');
		$field->attr('checked', $data['fallbackMode']);
		$field->label = __('Fallback mode');
		$field->description = __('Check this when you have troubles with generating image (e.g. GhostScript errors).');
		$field->notes = __('**Warning:** This will produce low quality images and some field type options won\'t be available, but may work where normal mode doesn\'t.');

		$inputfields->add($field);

		return $inputfields;
	}
}
