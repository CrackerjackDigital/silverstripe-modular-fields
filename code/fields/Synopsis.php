<?php
namespace Modular\Fields;

use \HtmlEditorField;
use Modular\Field;
use Modular\Types\StringType;

class Synopsis extends TypedField implements StringType {
	const Name = 'Synopsis';
	// const Schema = 'HTMLText';

	public function cmsFields($mode) {
		return [
			HtmlEditorField::create(static::field_name(), 'Synopsis')->setRows(5),
		];
	}
}
