<?php
namespace Modular\Fields;

use Modular\Types\StringType;

class ExternalReference extends \Modular\Field implements StringType {
	const SingleFieldName = 'ExternalReference';
	const SingleFieldSchema = 'Varchar(8)';
}