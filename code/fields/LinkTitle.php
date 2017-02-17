<?php
namespace Modular\Fields;

use Modular\Types\StringType;

class LinkTitle extends \Modular\Field implements StringType {
	const SingleFieldName = 'LinkTitle';
	const SingleFieldSchema = 'Varchar(255)';
}