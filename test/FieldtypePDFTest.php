<?php

/*
 * The MIT License
 *
 * Copyright 2016 Richard Jedlička (http://uiii.cz).
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

if (function_exists("\\ProcessWire\\wire")) {
	// ProcesWire 3.x
	class_alias("\\ProcessWire\\Field", "Field");
	class_alias("\\ProcessWire\\Pageimage", "Pageimage");
	function wire() {
		return call_user_func_array("\\ProcessWire\\wire", func_get_args());
	}
}

/**
 * PHPUnit test for FieldtypePDF ProcessWire module
 *
 * @backupGlobals disabled
 */
class FieldtypePDFTest extends PHPUnit_Framework_TestCase
{
	const FIELDTYPE_MODULE_NAME = 'FieldtypePDF';
	const INPUTFIELD_MODULE_NAME = 'InputfieldPDF';

	protected static $modulePath = PW_MODULES_PATH . self::FIELDTYPE_MODULE_NAME . DIRECTORY_SEPARATOR;
	protected static $fieldname = self::FIELDTYPE_MODULE_NAME . '_test';

	public static function setUpBeforeClass()
	{
		self::clean();

		$modules = wire('modules');

		if ($modules->isInstalled(self::FIELDTYPE_MODULE_NAME)) {
			error(sprintf('Module \'%s\' must not be installed', self::FIELDTYPE_MODULE_NAME));
		}

		if(! $modules->isInstallable(self::FIELDTYPE_MODULE_NAME, true)) {
			error(sprintf('Module \'%s\' must be present in the modules directory and all of it\'s dependencies must be installed', self::FIELDTYPE_MODULE_NAME));
		}
	}

	public static function tearDownAfterClass()
	{
		self::clean();
	}

	public function testInstall()
	{
		$modules = wire('modules');

		$this->assertTrue($modules->isInstallable(self::FIELDTYPE_MODULE_NAME));

		$modules->install(self::FIELDTYPE_MODULE_NAME);
		$modules->triggerInit();

		$this->assertTrue($modules->isInstalled(self::FIELDTYPE_MODULE_NAME));
		$this->assertTrue($modules->isInstalled(self::INPUTFIELD_MODULE_NAME));

		FieldtypePDF\PDFConverter::$defaultOptions['resolution'] = '50x50'; // for speed
	}

	/**
	 * @depends testInstall
	 */
	public function testAddField()
	{
		$field = new Field();
		$field->name = self::$fieldname;
		$field->type = wire('modules')->get(self::FIELDTYPE_MODULE_NAME);
		$field->save();

		$template = wire('templates')->get('home');
		$template->fieldgroup->add($field);
		$template->save();

		$this->assertTrue(wire('fields')->has($field->name));
	}

	/**
	 * @depends testAddField
	 */
	public function testAddFile()
	{
		$home = wire('pages')->get('/');

		$pdfFiles = $home->{self::$fieldname};
		$pdfFiles->add(TEST_ASSET_PATH . 'test.pdf');
		$home->save();

		$this->assertFileExists($pdfFiles->first()->filename);
		$this->assertStringStartsWith($pdfFiles->path, $pdfFiles->first()->filename);

		return $pdfFiles;
	}

	/**
	 * @depends testAddFile
	 */
	public function testCreateImage($pdfFiles)
	{
		$image = $pdfFiles->first()->toImage();

		$this->assertInstanceOf(Pageimage, $image);

		$generatedImage = new Imagick($image->filename);
		$testImage = new Imagick(TEST_ASSET_PATH . 'test.jpg');

		// images have the same size
		$this->assertEquals($generatedImage->getimagewidth(), $testImage->getimagewidth());
		$this->assertEquals($generatedImage->getimageheight(), $testImage->getimageheight());

		// images differ lesser than 0.5%
		$this->assertLessThan(0.005, $result = $generatedImage->compareimages($testImage, Imagick::METRIC_MEANABSOLUTEERROR)[1]);

		return $pdfFiles;
	}

	/**
	 * @depends testAddFile
	 */
	public function testCreateImageWithCustomOptions($pdfFiles)
	{
		$options = array(
			'suffix' => 'custom',
			'extenstion' => 'jpg',
			'format' => 'PNG',
			'background' => '#00FF00',
			'resolution' => '100x100',
		);

		$image = $pdfFiles->first()->toImage($options);

		$generatedImage = new Imagick($image->filename);
		$testImage = new Imagick(TEST_ASSET_PATH . 'test-custom.jpg');

		$this->assertEquals($options['format'], $generatedImage->getimageformat());
		$this->assertEquals($options['resolution'], implode('x', $generatedImage->getimageresolution()));

		// images have the same size
		$this->assertEquals($generatedImage->getimagewidth(), $testImage->getimagewidth());
		$this->assertEquals($generatedImage->getimageheight(), $testImage->getimageheight());

		// images differ lesser than 0.5%
		$this->assertLessThan(0.005, $result = $generatedImage->compareimages($testImage, Imagick::METRIC_MEANABSOLUTEERROR)[1]);

		return $pdfFiles;
	}

	/**
	 * @depends testAddFile
	 */
	public function testDeprecatedMethods($pdfFiles)
	{
		$pdfFile = $pdfFiles->first();

		$image = $pdfFile->toImage()->size(10, 10);
		$depracatedImage = $pdfFile->thumbnail(10, 10);

		$this->assertEquals($image->filename(), $depracatedImage->filename());
		$this->assertTrue($pdfFile->isThumbnail($depracatedImage));

		$depracatedImageBasename = $depracatedImage->basename;
		$pdfFile->removeImages();
		$this->assertTrue($pdfFile->isThumbnail($depracatedImageBasename));
	}

	/**
	 * @depends testCreateImageWithCustomOptions
	 */
	public function testDeleteFile($pdfFiles)
	{
		$filenames = array();

		$pdfFile = $pdfFiles->first();
		$filenames[] = $pdfFile->filename;

		// test file's image removal
		$image = $pdfFile->toImage();
		$filenames[] = $image->filename;

		// create thumbnail to also test it's removal
		$thumbnail = $image->size(10, 10);
		$filenames[] = $thumbnail->filename;

		$pdfFiles->remove($pdfFile);
		$pdfFiles->page->save();

		foreach ($filenames as $filename) {
			$this->assertFileNotExists($filename);
		}
	}

	/**
	 * @depends testInstall
	 */
	public function testPDFConverter()
	{
		$converter = new FieldtypePDF\PDFConverter('');
		$converter->setOptions(array(
			'resolution' => 200,
			'imagickOptions' => 'option'
		));

		$options = $converter->getOptions();

		$this->assertEquals(array(200, 200), $options['resolution']);
		$this->assertEquals(array('option'), $options['imagickOptions']);
	}

	/**
	 * @depends testInstall
	 */
	public function testPagePDFs()
	{
		$home = wire('pages')->get('/');
		$pdfFiles = $home->{self::$fieldname};
		$blankItem = $pdfFiles->makeBlankItem();

		$this->assertInstanceOf('FieldtypePDF\PagePDF', $blankItem);
	}

	/**
	 * @depends testInstall
	 */
	public function testConfigInputfields()
	{
		$field = new Field();
		$field->type = wire('modules')->get(self::FIELDTYPE_MODULE_NAME);

		$this->assertNotEmpty($field->getConfigInputfields());
	}

	protected static function clean()
	{
		$modules = wire('modules');

		$fields = wire('fields');
		foreach ($fields->find('name^=' . self::FIELDTYPE_MODULE_NAME) as $field) {
			foreach ($field->getFieldgroups() as $fieldgroup) {
				$fieldgroup->remove($field);
				$fieldgroup->save();
			}

			wire('pages')->get('/')->save();

			$fields->delete($field);
		}

		if ($modules->isInstalled(self::FIELDTYPE_MODULE_NAME)) {
			$modules->uninstall(self::FIELDTYPE_MODULE_NAME);
		}

		$modules->resetCache();
	}
}
