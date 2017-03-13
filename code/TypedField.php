<?php
namespace Modular;

use Modular\Types\RefType;
use Modular\Types\TypeInterface;

abstract class TypedField extends Field implements TypeInterface {
	public function extraStatics($class = null, $extension = null) {
		if (!$this instanceof RefType) {
			// if not a ref-type then return the 'Field' which is a db static
			return parent::extraStatics($class, $extension);
		}
	}

	/**
	 * Type is defined on an Modular\Type interface the field should implement
	 *
	 * @return string
	 */
	public static function type() {
		return static::Type;
	}

	/**
	 * Schema is defined on an Modular\Type interface the field should implement
	 *
	 * @return string
	 */
	public static function schema() {
		return static::Schema;
	}
}
