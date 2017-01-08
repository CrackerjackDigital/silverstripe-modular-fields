<?php
namespace Modular\Fields;

use Modular\Field;
use Modular\Types\BoolType;

/**
 * Flag field representing a Boolean database field
 *
 * @package modular\Fields
 */
abstract class Flag extends Options implements BoolType {
	const SingleFieldSchema = 'Boolean';
	const NoValue           = 0;
	const YesValue          = 1;

	const ShowAsCheckbox = 'Checkbox';

	private static $show_as = self::ShowAsCheckbox;

	private static $default_value = false;

	private static $default_options = [
		self::NoValue  => 'No',
		self::YesValue => 'Yes',
	];

	public function cmsFields($mode) {
		return [
			static::single_field_name() => $this->makeField(),
		];
	}

	public static function options($default = [], $override = []) {
		$options = parent::options();
		if (static::default_value()) {
			// sort so true option is first
			krsort($options);
		} else {
			// sort false option first
			ksort($options);
		}
	}

	/**
	 * @return \CheckboxField
	 */
	public function makeField() {
		// null is not a valid option so we can use the default here
		$value = is_null($this->singleFieldValue())
			? static::default_value()
			: $this->singleFieldValue();

		switch (static::config()->get('show_as')) {
		case self::ShowAsCheckbox:
			$field = new \CheckboxField(
				static::single_field_name(),
				'',
				$value
			);
			break;
		default:
			$field = parent::makeField();
		}
		return $field;
	}

}