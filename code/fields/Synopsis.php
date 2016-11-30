<?php
namespace Modular\Fields;

use \HtmlEditorField;

class Description extends \Modular\Field {
	const SingleFieldName = 'Description';
	const SingleFieldSchema = 'HTMLText';

	public function cmsFields() {
		return [
			HtmlEditorField::create(static::single_field_name(), 'Description')->setRows(5),
		];
	}
}
