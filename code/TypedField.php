<?php
namespace Modular;

use Modular\Types\Type;

abstract class TypedField extends Field implements Type {
	public function extraStatics($class = null, $extension = null) {
		return array_merge(
			parent::extraStatics($class, $extension) ?: [],
			[
				'db' => [
					static::field_name() => static::schema()
				]
			]
		);
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
