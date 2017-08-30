<?php
namespace Modular\Fields;

use Modular\TypedField;
use Modular\Types\StringType;

class Title extends TypedField implements StringType {
	const Name = 'Title';

	// convenience
	const TitleFieldName = self::Name;

	private static $fields_for_action = [
		\Modular\Interfaces\Action::ActionCreate => [
			self::Name => true
		],
		\Modular\Interfaces\Action::ActionEdit => [
			self::Name => true
		],
		\Modular\Interfaces\Action::ActionView => [
			self::Name => true
		]
	];

}