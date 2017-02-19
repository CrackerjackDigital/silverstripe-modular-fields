<?php
namespace Modular\Fields;

use Modular\Types\StringType;

class Enum extends Options implements StringType {

	/**
	 * For an enum field the schema is an Enum of all options. Numeric enum values are not allowed.
	 *
	 * @return string
	 */
	public static function schema() {
		if ($options = array_filter(static::options())) {
			return "Enum('" . implode(',', $options) . "')";
		}
		return '';
	}

}