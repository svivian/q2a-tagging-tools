<?php
/*
	Plugin Name: Tagging Tools
	Plugin URI: https://github.com/svivian/q2a-tagging-tools
	Plugin Description: Automatically modify/remove tags in questions
	Plugin Version: 1.8
	Plugin Date: 2015-07-29
	Plugin Author: Scott Vivian
	Plugin Author URI: http://codelair.com/
	Plugin License: GPLv3
	Plugin Minimum Question2Answer Version: 1.6
	Plugin Update Check URI: https://raw.githubusercontent.com/svivian/q2a-tagging-tools/master/qa-plugin.php

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.gnu.org/licenses/gpl.html
*/

if (!defined('QA_VERSION')) exit;


qa_register_plugin_module('filter', 'qa-tagging-tools.php', 'qa_tagging_tools', 'Tagging Tools');
qa_register_plugin_module('page', 'qa-tt-ajax.php', 'qa_tagging_tools_ajax', 'Tagging Tools AJAX handler');
qa_register_plugin_layer('qa-tt-layer.php', 'Tagging Tools Layer');
qa_register_plugin_phrases('qa-tt-lang-*.php', 'taggingtools');
