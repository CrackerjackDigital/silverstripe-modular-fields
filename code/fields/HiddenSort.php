<?php
namespace Modular\Fields;

use Modular\Types\IntType;

class HiddenSort extends \Modular\Field implements IntType {
	const SingleFieldName = 'Sort';
	const SingleFieldSchema = 'Int';
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
			$fields[ static::SingleFieldName ] = new \HiddenField(static::SingleFieldName);
			return $fields;
		}
	}
}