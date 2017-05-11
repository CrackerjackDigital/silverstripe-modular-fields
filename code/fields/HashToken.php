<?php

namespace Modular\Fields;

use Modular\Interfaces\ValueGenerator;
use Modular\Traits\generator;
use Modular\Traits\md5;
use Modular\Types\StringType;

class HashToken extends \Modular\TypedField implements ValueGenerator, StringType {
	use generator;
	use md5 {
		hash as generator;
	}

	const Name = 'HashToken';

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