<?php

namespace Modular\Fields;

use Modular\TypedField;
use Modular\Types\URLType;

class ExternalLink extends TypedField implements URLType {
	const ExternalLinkOption = 'ExternalLink';
	const Name               = 'ExternalLink';

	/**
	 * If value does not have a schema then prefix 'file://'
	 *
	 * @param null $typeCast ignored
	 *
	 * @return mixed|string
	 * @throws \Modular\Exceptions\Exception
	 */
	public function typedValue( $typeCast = null ) {
		$value = $this->singleFieldValue();
		if ( false == strpos( $value, '://' ) ) {
			$value = static::TypedValuePrefix . $value;
		}

		return $value;
	}

}