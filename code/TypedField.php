<?php
namespace Modular;

use Modular\Exceptions\TypeException;
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
	 * @throws \Modular\Exceptions\TypeException
	 */
	public static function type() {
		if ( ! defined( 'static::Type' ) ) {
			throw new TypeException("No Type" );
		}

		return static::Type;
	}

	/**
	 * Schema is defined on an Modular\Type interface the field should implement
	 *
	 * @return string
	 * @throws \Modular\Exceptions\TypeException
	 */
	public static function schema() {
		if (!defined('static::Schema')) {
			throw new TypeException("No Schema");
		}
		return static::Schema;
	}
}
