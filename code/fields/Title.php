<?php
namespace Modular\Fields;

use Modular\Types\StringType;
use TextField;

class Title extends \Modular\TypedField implements StringType {
	const Name = 'Title';
	// const Schema = 'Varchar(255)';

	// convenience
	const TitleFieldName = self::Name;

}