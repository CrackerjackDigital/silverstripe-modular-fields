<?php
namespace Modular\Fields;

use \HtmlEditorField;
use Modular\Types\StringType;

class Synopsis extends \Modular\Field implements StringType {
	const FieldName = 'Synopsis';

	private static $db = [
		self::FieldName => 'HTMLText',
	];
	public function cmsFields($mode) {
		return [
			HtmlEditorField::create('Synopsis', 'Synopsis')->setRows(5),
		];
	}
}
