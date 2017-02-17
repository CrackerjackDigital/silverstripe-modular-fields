<?php
namespace Modular\Fields;

use Modular\Types\StringType;
use TextField;

class Title extends \Modular\Field implements StringType {
	const SingleFieldName = 'Title';
	const SingleFieldSchema = 'Varchar(255)';

	// convenience
	const TitleFieldName = self::SingleFieldName;

}