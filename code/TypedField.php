<?php
namespace Modular;

use Modular\Types\RefOneType;
use Modular\Types\RefType;
use Modular\Types\TypeInterface;

abstract class TypedField extends Field implements TypeInterface {
	public function extraStatics($class = null, $extension = null) {
		if (!$this instanceof RefType) {
			return parent::extraStatics( $class, $extension );
		}
	}

	public function onBeforeValidate() {
		// TODO validate by Type
		return true;
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
