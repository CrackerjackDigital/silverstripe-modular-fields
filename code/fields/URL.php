<?php
namespace Modular\Fields;

use Modular\TypedField;
use Modular\Types\URLType;

class URL extends TypedField implements URLType {
	const Name = 'URL';

	/**
	 * If value does not have a schema then prefix 'https://'
	 * @param null $typeCast ignored
	 * @return mixed|string
	 * @throws \Modular\Exceptions\Exception
	 */
	public function typedValue($typeCast = null) {
		$value = $this->typedValue();
		if (false == strpos($value, '://')) {
			$value = "https://$value";
		}
		return $value;
	}

}