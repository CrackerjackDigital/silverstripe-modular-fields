<?php
namespace Modular\Fields;

use Modular\TypedField;
use Modular\Types\StringType;

class Title extends TypedField implements StringType {
	const Name = 'Title';

	// convenience
	const TitleFieldName = self::Name;

}