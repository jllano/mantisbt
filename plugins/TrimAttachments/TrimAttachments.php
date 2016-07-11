<?php
#
# Copyright (c) 2016 MantisHub
# Licensed under the MIT license
#

class TrimAttachmentsPlugin extends MantisPlugin
{
	function register() {
		$this->name = plugin_lang_get( 'title' );
		$this->description = plugin_lang_get( 'description' );
		$this->page = 'trim_attachments';
		$this->version = '1.0';
		$this->requires = array( 'MantisCore' => '2.0.0' );
		$this->author = 'MantisHub';
		$this->contact = 'http://www.mantishub.com';
		$this->url = 'http://www.mantishub.com';
	}
}
