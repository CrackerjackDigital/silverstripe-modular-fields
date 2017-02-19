<?php
namespace Modular\Fields;
use Modular\Types\StringType;

/**
 * A field which shows and validates a particular format, such as a phone number or registration number.
 *
 * @package Modular\Fields
 */
abstract class Formatted extends \Modular\TypedField implements StringType {
	// const Schema = 'Varchar(32)';
}