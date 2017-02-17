<?php
namespace Modular\Fields;

use Modular\Field;
use Modular\Types\StringType;

class Content extends Field implements StringType {
	const SingleFieldName   = 'Content';
	const SingleFieldSchema = 'HTMLText';

}
