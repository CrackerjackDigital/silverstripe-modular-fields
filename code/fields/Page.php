<?php
namespace Modular\Fields;

use Modular\TypedField;
use Modular\Types\RefOneType;

class Page extends TypedField implements RefOneType {
	const Name   = 'Page';
	const Schema = 'Page';
}