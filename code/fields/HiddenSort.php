<?php
namespace Modular\Fields;

use Modular\Types\IntType;

class HiddenSort extends \Modular\TypedField implements IntType {
	const Name = 'Sort';
	// const Schema = 'Int';
	const ReadOnly = true;

	/**
	 * In CMS replace the field with a Read Only field.
	 *
	 * @param $mode
	 * @return array
	 */
	public function cmsFields($mode) {
		if (static::ReadOnly) {
			$fields = parent::cmsFields($mode);
			$fields[ static::Name ] = new \HiddenField(static::Name);
			return $fields;
		}
	}
}