<?php
namespace Modular\Fields;

use Modular\TypedField;
use Modular\Model;
use Modular\Types\StringType;

class UniqueField extends TypedField implements StringType {
	/**
	 * Always a ReadonlyField
	 *
	 * @param $mode
	 * @return array
	 */
	public function cmsField($mode = null) {
		return [
			new \ReadonlyField(static::Name)
		];
	}

	/**
	 * A unique field should have an index, however it isn't a unique index as e.g. the field
	 * may only be unique at one level of a heirarchy or across model classes.
	 *
	 * @param null $class
	 * @param null $extension
	 * @return array
	 * @throws \Modular\Exceptions\Exception
	 */
	public function extraStatics($class = null, $extension = null) {
		return array_merge_recursive(
			parent::extraStatics($class, $extension) ?: [],
			[
				'indexes' => [
					static::field_name() => true
				]
			]
		);
	}

	/**
	 * Prevent duplicate code being entered.
	 *
	 * @param \ValidationResult $result
	 * @return array|void
	 * @throws \ValidationException
	 */
	public function validate(\ValidationResult $result) {
		// this could throw an exception, let it
		parent::validate($result);

		$fieldName = static::field_name();

		$value = $this()->{$fieldName};

		if ($this()->isInDB()) {
			// code should be read-only in CMS but check anyway that doesn't exist on another ID
			$existing = Model::get($this()->class)
				->exclude('ID', $this()->ID)
				->filter($fieldName, $value)
				->first();
		} else {
			// check code doesn't exist already
			$existing = Model::get($this()->class)
				->filter($fieldName, $value)
				->first();
		}
		if ($existing) {
			$message = $this->fieldDecoration(
				$fieldName,
				'Duplicate',
				"Field '$fieldName' must be unique, the {singular} '{title}' already uses '{code}'", [
					'code'  => $value,
					'title' => $existing->Title ?: $existing->Name
				]
			);

			$result->error($message);
		}
	}
}