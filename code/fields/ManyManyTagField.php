<?php
namespace Modular\Fields;
use Modular\Forms\TagField;
use Modular\Relationships\HasManyMany;

/**
 * Adds a tag field representation of a HasManyMany relationship
 *
 * @package Modular\Fields
 */

class HasManyManyTagField extends HasManyMany {
	private static $multiple_tags = true;
	private static $can_create_tags = true;

	public function cmsField($mode = null) {
		return [
			(new TagField(
				static::field_name(),
				'',
				$this->availableTags()
			))->setIsMultiple(
				(bool) $this->config()->get('multiple_tags')
			)->setCanCreate(
				(bool) $this->config()->get('can_create_tags')
			),
		];
	}

	protected function availableTags() {
		$tagClassName = static::schema();
		return $tagClassName::get()->sort('Title');
	}
}