<?php
namespace Modular\Fields;

abstract class EnumField extends \Modular\Field {
	const SingleFieldName = '';

	/**
	 * Can be numerically indexed or associative.
	 * If numeric, then values will be used as both key and value in dropdown
	 * If assoc then key will be value and value will be display in dropdown
	 *
	 * First option will be the default/empty value
	 */

	private static $options = [];

	/**
	 * For an enum field the schema is an Enum of all options
	 * @return string
	 */
	public static function single_field_schema()
	{
		if ($options = array_filter(array_keys(static::options()))) {
			return "Enum('" . implode(',', $options) . "')";
		}
		return '';
	}

	public function cmsFields()
	{
		return [
			(new \DropdownField(
				static::single_field_name(),
				null,
				$this->dropdownMap()
			))->setEmptyString(current($this->options()))
		];
	}

	/**
	 * Transform options by checking lang file for <FieldName>.Options.<Key> for each option
	 * @return array
	 */
	public function dropdownMap()
	{
		$options = static::options();
		// now lookup translations as the value
		return array_map(
			function($value, $key) {
				return _t(static::single_field_name() . '.Options.' . $key, $value);
			},
			array_values($options),
			array_keys($options)
		);
	}

	/**
	 * Always return an associative map of options even if configured as an array.
	 * @return array
	 */
	public static function options() {
		if ($options = static::config()->get('options') ?: []) {
			if (is_numeric(key($options))) {
				$options = array_combine(
					array_values($options),
					array_values($options)
				);
			}
		}
		return $options ?: [];
	}

}