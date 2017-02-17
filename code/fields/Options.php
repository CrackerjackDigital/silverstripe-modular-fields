<?php
namespace Modular\Fields;

use Modular\Field;

abstract class Options extends Field  {
	// the name of the field, if RelatedClassName is set then this will be used as the ID field on has_one relationships
	const SingleFieldName = '';
	// for has_one fields set this if you want the option selector field to show models from this class,
	// IDs will be the key, Titles the value.
	// NB:: The result of single_field_name will also have an 'ID' appended automatically
	const RelatedClassName = '';

	const ShowAsDropdown = 'Dropdown';
	const ShowAsRadio    = 'Radio';

	// default to show as a dropdown
	private static $show_as = self::ShowAsDropdown;

	/**
	 * Can be numerically indexed or associative.
	 * If numeric, then values will be used as both key and value in dropdown
	 * If assoc then key will be value and value will be display in dropdown
	 *
	 * First option will be the default/empty value
	 */

	private static $options = [];

	private static $default_value;

	private static $empty_string;

	// if we're showing related by setting RelatedClassName then sort those models by this
	private static $options_sort = 'Title asc';

	// will be appled as a filter to options if RelatedClassName is set and so options are those models
	private static $options_filter = [];

	// will be used as key => value for options if RelatedClassName is set
	private static $options_fields = ['ID', 'Title'];

	public function cmsFields($mode) {
		return [
			$this->makeField(),
		];
	}

	/**
	 * Returns options to show in dropdown, option set etc
	 *
	 * @return array
	 */
	public function optionMap() {
		return static::options();
	}

	public static function default_value() {
		return static::config()->get('default_value');
	}

	/**
	 * Return a field of type depending on config.show_as setting or null if setting not handled.
	 *
	 * @return \DropdownField|\OptionsetField|null
	 */
	protected function makeField() {
		switch ($this->config()->get('show_as')) {
		case self::ShowAsDropdown:
			$field = new \DropdownField(
				static::single_field_name(),
				'',
				static::options()
			);
			if ($emptyString = static::config()->get('empty_string')) {
				$field->setEmptyString($emptyString);
			}
			break;
		case self::ShowAsRadio:
			$field = new \OptionsetField(
				static::single_field_name(),
				'',
				static::options()
			);
			// if it is null then nothing will be set which is fine anyway
			if (!is_null($defaultValue = static::default_value())) {
				// TODO do we need this to set if no value, what happens if there is?
//				$field->setValue($defaultValue);
			}
			break;
		default:
			$field = null;
		}
		return $field;
	}

	/**
	 * If RelatedClassName then return field name with 'ID' appended, otherwise just the field name. This will only
	 * work properly if the relationship is a 'has_one' and could break otherwise unless you set suffix to '' explicitly.
	 *
	 * @param string $suffix
	 * @return mixed|string
	 */
	public static function single_field_name($suffix = 'ID') {
		if (static::RelatedClassName) {
			$name = parent::single_field_name($suffix);
		} else {
			// if suffix is 'ID' then remove as that would probably be illegal
			// you could force it by supplying IDID though that would be a smell
			$name = parent::single_field_name($suffix == 'ID' ? substr($suffix, 0, -2) : $suffix);
		}
		return $name;
	}

	/**
	 * Return related class title and id map if RelatedClassName is set, otherwise options from the
	 * config.options with values translated if language key is found for input value.
	 *
	 * @param array|null $default   use this as the default map instead of config.default_value.
	 *                              If not in the returned options will be added as first entry.
	 *                              If null it will not be prepended.
	 * @param array      $override  use these instead of the options from config. usefull if calling this from a derived class
	 *                              which has config.options structured differently.
	 *                              Title translation will still occur on the supplied title.
	 *                              Can also be passed as empty array to have no options.
	 * @return array
	 */
	public static function options($default = [], $override = []) {
		if (static::RelatedClassName) {

			$optionFields = static::option_fields();

			$options = \DataObject::get(static::RelatedClassName)
				->filter(static::options_filter())
				->sort(static::options_sort())
				->map(key($optionFields), current($optionFields))
				->toArray();

		} else {
			// will use override if 2 or more arguments passed
			$options = array_map(
				function ($labelOrLangKey) {
					return _t(get_called_class() . ".$labelOrLangKey", $labelOrLangKey);
				},
				(func_num_args() >= 2) ? $override : static::config()->get('options')
			);
		}
		if (func_num_args() >= 1) {
			// at least default passed
			if (is_array($default)) {
				if (!array_key_exists(key(reset($default)), $options)) {
					$options = $default + $options;
				}
			}
		}

		return $options;
	}

	public static function options_sort() {
		return static::config()->get('options_sort');
	}
	/**
	 * Return a map of filters to apply to option selector used if RelatedClassName is set suitable for use by ORM filter method.
	 * By default an empty filter '[]'.
	 *
	 * @return array
	 */
	public static function options_filter() {
		return static::config()->get('options_filter');
	}

	public static function option_fields() {
		return static::config()->get('option_fields');
	}

}