<?php
namespace Modular\Fields;

use \HtmlEditorField;
use Modular\Types\StringType;
use Modular\TypedField;

class Description extends TypedField implements StringType {
	const Name = 'Description';

	public function cmsField($mode = null) {
		return [
			HtmlEditorField::create(static::Name)->setRows(5),
		];
	}
}
