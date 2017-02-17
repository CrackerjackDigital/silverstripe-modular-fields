<?php
namespace Modular\Fields;
use Modular\Types\StringType;

/**
 * A field which shows and validates a particular format, such as a phone number or registration number.
 *
 * @package Modular\Fields
 */
class Formatted extends \Modular\Field implements StringType {
	const SingleFieldSchema = 'Varchar(32)';
}