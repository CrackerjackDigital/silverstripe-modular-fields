<?php
namespace Modular\Fields;

use \HtmlEditorField;
use Modular\Types\StringType;

class Description extends \Modular\Field implements StringType {
	const FieldName = 'Description';

	private static $db = [
		self::FieldName => 'HTMLText'
	];
	public function cmsFields($mode) {
		return [
			HtmlEditorField::create(static::FieldName)->setRows(5),
		];
	}
}
