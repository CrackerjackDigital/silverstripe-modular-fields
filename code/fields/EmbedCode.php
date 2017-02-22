<?php
namespace Modular\Fields;

use Modular\TypedField;
use Modular\Types\StringType;
use TextField;

class EmbedCode extends TypedField implements StringType {
	const EmbedCodeFieldName = 'EmbedCode';
	const EmbedCodeOption    = 'EmbedCode';

	private static $db = [
		self::EmbedCodeFieldName => 'Text'
	];

	public function cmsField($mode = null) {
		return [
			new TextField(self::EmbedCodeFieldName)
		];
	}
}