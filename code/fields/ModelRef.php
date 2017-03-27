<?php
namespace Modular\Fields;

use Modular\TypedField;
use Modular\Types\RefOneType;

/**
 * A ModelRef field holds a reference to another Model/DataObject of any type
 *
 * @package Modular\Fields
 */
class ModelRef extends TypedField implements RefOneType {
	const Name = 'ModelRef';
	const Schema = \PolymorphicForeignKey::class;

}