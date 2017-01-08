<?php
namespace Modular\Fields;

use \HtmlEditorField;
use Modular\Field;
use Modular\Types\StringType;

class Synopsis extends Field implements StringType {
	const SingleFieldName = 'Synopsis';
	const SingleFieldSchema = 'HTMLText';

	public function cmsFields($mode) {
		return [
			HtmlEditorField::create(static::single_field_name(), 'Synopsis')->setRows(5),
		];
	}
}
