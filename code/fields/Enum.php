<?php
namespace Modular\Fields;

use Modular\Types\OptionType;

abstract class Enum extends Options implements OptionType {

	/**
	 * For an enum field the schema is an Enum of the keys of the top level options.
	 *
	 * @return string
	 */
	public static function schema() {
		$schema = '';
		if ($options = static::options()) {
			if (is_int(key($options))) {
				$options = array_values( $options );
			} else {
				$options = array_keys( $options );
			}
			$schema = "Enum('" . implode( ',', $options ) . "','" . current( $options ) . "')";
		}
		return $schema;
	}

}