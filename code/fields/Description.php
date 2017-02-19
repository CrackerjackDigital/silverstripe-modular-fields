<?php
namespace Modular\Fields;

use \HtmlEditorField;
use Modular\Types\StringType;

class Description extends TypedField implements StringType {
	const Name = 'Description';

	public function cmsFields($mode) {
		return [
			HtmlEditorField::create(static::Name)->setRows(5),
		];
	}
}
