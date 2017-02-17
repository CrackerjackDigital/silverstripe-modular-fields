<?php
namespace Modular\Fields;

use Modular\Field;
use Modular\Types\URLType;

class ExternalLink extends Field implements URLType {
	const ExternalLinkOption    = 'ExternalLink';
	const SingleFieldName       = 'ExternalLink';
	const SingleFieldSchema     = 'Text';
	
	/**
	 * If value does not have a schema then prefix 'file://'
	 * @param null $typeCast ignored
	 * @return mixed|string
	 * @throws \Modular\Exceptions\Exception
	 */
	public function typedValue($typeCast = null) {
		$value = $this->singleFieldValue();
		if (false == strpos($value, '://')) {
			$value = static::TypedValuePrefix . $value;
		}
		return $value;
	}
	

}