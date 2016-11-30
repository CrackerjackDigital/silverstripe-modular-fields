<?php
namespace Modular\Fields;

use Modular\Model;

class UniqueField extends \Modular\Field {

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

		$fieldName = static::single_field_name();

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