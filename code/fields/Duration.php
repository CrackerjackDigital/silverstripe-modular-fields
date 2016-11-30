<?php
namespace Modular\Fields;

use TextField;

class Duration extends \Modular\Field {
	const DurationFieldName = 'Duration';

	private static $db = [
		self::DurationFieldName => 'Varchar(10)'
	];

	public function cmsFields() {
		return [
			new TextField(self::DurationFieldName)
		];
	}
}
