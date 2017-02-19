<?php
namespace Modular\Fields;

use Modular\Field;
use Modular\Types\URNType;

class URL extends TypedField implements URNType {
	const Name = 'URL';

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