<?php
namespace Modular\Fields;

use Modular\Field;
use Modular\Types\URNType;

class URL extends Field implements URNType {
	const SingleFieldName = 'URL';
	const SingleFieldSchema = 'Text';
	
	/**
	 * If value does not have a schema then prefix 'https://'
	 * @param null $typeCast ignored
	 * @return mixed|string
	 * @throws \Modular\Exceptions\Exception
	 */
	public function typedValue($typeCast = null) {
		$value = $this->singleFieldValue();
		if (false == strpos($value, '://')) {
			$value = "https://$value";
		}
		return $value;
	}
	
}