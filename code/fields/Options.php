<?php
namespace Modular\Fields;

use ClassInfo;
use DataObject;
use Modular\TypedField;

abstract class Options extends TypedField {
	// the name of the field, if Schema is set then this will be used as the ID field on has_one relationships
	const Name = '';

	// for has_one fields set this if you want the option selector field to show models from this class,
	// IDs will be the key, Titles the value.
	// NB:: The result of field_name will also have an 'ID' appended automatically
	//	const Schema = '';

	const ShowAsDropdown = 'Dropdown';
	const ShowAsRadio    = 'Radio';

	// default to show as a dropdown
	private static $show_as = self::ShowAsDropdown;

	/**
	 * Can be numerically indexed or associative.
	 * If numeric, then values will be used as both key and value in dropdown
	 * If assoc then key will be the value and the value will be display in dropdown
	 *
	 * First option will be the default/empty value and displayed, unless default_value/empty_string is set.
	 */
	private static $options = [];

	/**
	 * If no option has been selected then use this with the corresponding value from options,
	 * or if this value is not in options then either empty_string if that is set or an empty string.
	 */
	private static $default_value;

	/**
	 * If no option has been selected then show this with either an empty value or with default_value if that is set.
	 */
	private static $empty_string;

	// if we're showing related by setting Schema then sort those models by this
	private static $options_sort = 'Title asc';

	// will be appled as a filter to options if Schema is set and so options are those models
	private static $options_filter = [];

	// will be used as key => value for options if Schema is set
	private static $options_fields = [ 'ID', 'Title' ];

	public function cmsField( $mode = null ) {
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
		return static::config()->get( 'default_value' );
	}

	public static function empty_string() {
		return static::config()->get( 'empty_string' );
	}

	/**
	 * Figure out and return a map of 'value' => 'display value' from from default_value and empty_string, or the first option if neither is set.
	 */
	public static function default_option() {
		$defaultValue = static::default_value();
		$emptyString  = static::empty_string();
		$options = static::config()->get('options');

		if ( !is_null($defaultValue) ) {
			// default value is set
			if ( in_array( $defaultValue, static::config()->get( 'options' ) ?: [] ) ) {
				// default value exists in options, use it and its value for display, or empty string as display if set
				$option = [
					$defaultValue => $emptyString ?: static::config()->get( 'options' )[ $defaultValue ],
				];
			} else {
				// default value doesn't exist in options, use it and empty string
				$option = [
					$defaultValue => $emptyString
				];
			}
		} elseif (!is_null($emptyString)) {
			// no default value set, but empty string set, use null and emptyString
			$option = [
				null => $emptyString
			];
		} else {
			// no default value or empty string, use first options
			$option = [
				key($options) => current($options)
			];
		}
		return $option;
	}

	/**
	 * Return a field of type depending on config.show_as setting or null if setting not handled.
	 *
	 * @return \DropdownField|\OptionsetField|null
	 */
	protected function makeField() {
		switch ( $this->config()->get( 'show_as' ) ) {
			case self::ShowAsDropdown:
				$field = new \DropdownField(
					static::field_name(),
					'',
					static::options()
				);
				if ( $emptyString = static::config()->get( 'empty_string' ) ) {
					$field->setEmptyString( $emptyString );
				}
				break;
			case self::ShowAsRadio:
				$field = new \OptionsetField(
					static::field_name(),
					'',
					static::options()
				);
				// if it is null then nothing will be set which is fine anyway
				if ( $defaultValue = static::config()->get('default_value') ) {
					$field->setValue( $defaultValue );
				}
				break;
			default:
				$field = null;
		}

		return $field;
	}

	/**
	 * If Schema then return field name with 'ID' appended, otherwise just the field name. This will only
	 * work properly if the relationship is a 'has_one' and could break otherwise unless you set suffix to '' explicitly.
	 *
	 * @param string $suffix
	 *
	 * @return mixed|string
	 */
	public static function field_name( $suffix = '' ) {
		$schema = static::schema();

		if ( \ClassInfo::exists( $schema ) ) {
			// schema is a class so we want field with 'ID'
			$name = parent::field_name( 'ID' );
		} else {
			// if suffix is 'ID' then remove as that would probably be illegal
			// you could force it by supplying IDID though that would be a smell
			$name = parent::field_name();
		}

		return $name;
	}

	/**
	 * Return a map of [ option => Title ] from config.options with values translated if language key is found for input value.
	 * If the options class is a model then will use a list of those models instead by ID => Title
	 *
	 * @param array|null $default   use this as the default map instead of config.default_values.
	 *                              If not in the returned options will be added as first entry.
	 *                              If null it will not be prepended.
	 * @param array      $override  use these instead of the options from config. usefull if calling this from a derived class
	 *                              which has config.options structured differently.
	 *                              Title translation will still occur on the supplied title.
	 *                              Can also be passed as empty array to have no options.
	 *
	 * @return array
	 */
	public static function options( $default = [], $override = [] ) {
		// try hard-coded options first, will init to an empty array if not set.
		if ( $options = static::config()->get( 'options' ) ?: [] ) {
			// will use override if it was passed
			$options = array_map(
				function ( $key ) {
					return _t( get_called_class() . ".$key", $key );
				},
				array_keys(( func_num_args() >= 2 ) ? $override : $options)
			);

		} else if ( $schema = static::schema() ) {
			// schema is another model not string values, us those models as the options
			$optionFields = static::option_fields();

			if ( ClassInfo::exists( $schema ) && is_a( $schema, DataObject::class, true ) ) {
				$options = \DataObject::get( $schema )
				                      ->filter( static::options_filter() )
				                      ->sort( static::options_sort() )
				                      ->map( key( $optionFields ), current( $optionFields ) )
				                      ->toArray();
			}

		}
		if ( func_num_args() >= 1 ) {
			// at least default passed
			if ( is_array( $default ) ) {
				if ( ! array_key_exists( key( reset( $default ) ), $options ) ) {
					$options = $default + $options;
				}
			}
		}

		return $options;
	}

	public static function options_sort() {
		return static::config()->get( 'options_sort' );
	}

	/**
	 * Return a map of filters to apply to option selector used if Schema is set suitable for use by ORM filter method.
	 * By default an empty filter '[]'.
	 *
	 * @return array
	 */
	public static function options_filter() {
		return static::config()->get( 'options_filter' );
	}

	public static function option_fields() {
		return static::config()->get( 'option_fields' );
	}

}