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

	public function cmsFields($mode) {
		return [
			new TextField(self::EmbedCodeFieldName)
		];
	}
}