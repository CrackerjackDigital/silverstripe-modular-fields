<?php
namespace Modular\Fields;

class Enum extends Options {

	/**
	 * For an enum field the schema is an Enum of all options. Numeric enum values are not allowed.
	 *
	 * @return string
	 */
	public static function single_field_schema() {
		if ($options = array_filter(static::options())) {
			return "Enum('" . implode(',', $options) . "')";
		}
		return '';
	}

}