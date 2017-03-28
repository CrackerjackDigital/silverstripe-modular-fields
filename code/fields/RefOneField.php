<?php
namespace Modular\Fields;

use Modular\TypedField;
use Modular\Types\RefOneType;

class RefOneField extends TypedField implements RefOneType {
	/**
	 * Add has_one relationships to related class.
	 *
	 * @param null $class
	 * @param null $extension
	 *
	 * @return mixed
	 */
	public function extraStatics( $class = null, $extension = null ) {
		return array_merge_recursive(
			parent::extraStatics( $class, $extension ) ?: [],
			[
				'has_one' => [
					static::relationship_name() => static::related_class_name(),
				],
			]
		);
	}

	/**
	 * Return self.Name or if not set the final component of the Namespaced called class name (or the called class name if no namespace).
	 *
	 * In RefOneField type the suffix defaults to 'ID'
	 *
	 * @param string $suffix appended if supplied
	 *
	 * @return string
	 */
	public static function field_name( $suffix = 'ID' ) {
		return parent::field_name($suffix);
	}

	/**
	 * has_one relationships need an 'ID' appended to the relationship name to make the field name
	 *
	 * @param string $suffix defaults to 'ID'
	 *
	 * @return string
	 */
	public static function related_field_name( $suffix = 'ID' ) {
		return static::field_name($suffix);
	}

	/**
	 * Return unadorned has_one related class name.
	 *
	 * @return string
	 */
	public static function related_class_name() {
		return static::schema();
	}

	/**
	 * Returns the Name for this field if set, optionally appended with the fieldName as for a relationship.
	 *
	 * @param string $fieldName if supplied will be added on to Name with a '.' prefix
	 *
	 * @return string
	 */
	public static function relationship_name( $fieldName = '' ) {
		return static::field_name('') ? ( static::field_name('') . ( $fieldName ? ".$fieldName" : '' ) ) : '';
	}

}
