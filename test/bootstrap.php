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

define('SRC_PATH', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
define('TEST_PATH', __DIR__ . DIRECTORY_SEPARATOR);
define('TEST_ASSET_PATH', TEST_PATH . 'asset' . DIRECTORY_SEPARATOR);

define('PW_PATH', rtrim(getenv('PW_PATH'), DIRECTORY_SEPARATOR));
define('PW_MODULES_PATH', PW_PATH . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR);

function error($message) {
	echo sprintf("[ERROR] %s\n", $message);
	exit(1);
}

$pwIndexFile = PW_PATH . DIRECTORY_SEPARATOR . 'index.php';
if (! PW_PATH || ! is_file($pwIndexFile)) {
	error('Please specify path to the ProcessWire\'s root directory (using PW_PATH environment variable');
}

include($pwIndexFile);