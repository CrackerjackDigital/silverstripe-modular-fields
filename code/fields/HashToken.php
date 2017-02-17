<?php

namespace Modular\Fields;

use Modular\Interfaces\ValueGenerator;
use Modular\Traits\generator;
use Modular\Traits\md5;
use Modular\Types\StringType;

class HashToken extends \Modular\Field implements ValueGenerator, StringType {
	use generator;
	use md5 {
		md5 as generator;
	}
	
	const SingleFieldName = 'HashToken';
	const SingleFieldSchema = 'Varchar(128)';

	private static $max_length = 128;
	
	private static $generate_always = false;

	/**
	 * If HashToken is not set on the model then generate a new one.
	 */
	public function onBeforeWrite()
	{
		parent::onBeforeWrite();
		if ($this->shouldGenerate()) {
			$this->singleFieldValue($this->generator(uniqid()));
		}
	}
	
	public static function max_length() {
		return static::config()->get('max_length') ?: 0;
	}
}