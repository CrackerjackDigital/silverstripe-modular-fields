<?php

namespace Modular\Fields;

/**
 * RefOneAnyField is a reference to a DataObject which will be used with PolymorphicForeignKey
 *
 * @package Modular\Fields
 */
class RefOneAnyField extends RefOneField {
	const Schema = 'DataObject';

	public function cmsFields( $mode = null ) {
		$field = \PolymorphicForeignKey::create( static::field_name( '' ), $this() );

		$fields[ static::field_name( '' ) ] = $field;

		return $fields;
	}

}