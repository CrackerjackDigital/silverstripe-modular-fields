<?php

namespace Modular\Fields;

/**
 * RefOneAnyField is a reference to a DataObject which will be used with PolymorphicForeignKey
 *
 * @package Modular\Fields
 */
abstract class RefOneAnyField extends RefOneField {
	const Schema = 'DataObject';

	public function cmsFields( $mode = null ) {
		$field = \PolymorphicForeignKey::create( static::field_name( '' ) );

		$fields[ static::field_name( '' ) ] = $field;

		return [];
	}

	/**
	 * Return the name of the field used to store the class of the referenced model.
	 *
	 * @return string
	 */
	public static function class_field_name() {
		return static::field_name( 'Class' );
	}

}