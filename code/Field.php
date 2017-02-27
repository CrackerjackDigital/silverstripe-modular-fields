<?php
namespace Modular;

use DateField;
use DatetimeField;
use FieldList;
use FormField;
use LiteralField;
use Modular\Exceptions\Exception;
use Modular\Interfaces\HasMode;
use Modular\Traits\bitfield;
use Modular\Traits\lang;
use Modular\Traits\value;
use SS_List;
use TimeField;
use ValidationException;
use ValidationResult;

/**
 * Validation rules from the extensions config.validation are formatted as a map of:
 *
 *  'FieldName' => [ minlength, maxlength, pattern ]
 *
 * - or -
 *
 *  'FieldName' => true | false
 *
 * where pattern is a preg expression and minlength/maxlength are integers (may be 0 for don't care)
 * - a minlength of > 0 or a boolean true means required
 * - a maxlength of 0 means no limit
 *
 * @property \Modular\Model $owner
 */
abstract class Field extends ModelExtension {
	use lang;
	use bitfield;
	use value;

	const ShowAsReadOnlyFlag = 1;

	// used to generate a field name for tracking changes in this fields value in onAfterWrite
	const PreviousFieldNamePrefix = '__';

	// if Name and Schema are provided then they will be added to the
	// config.db array by extraStatics
	const Name = '';

	const DefaultUploadFolderName = 'incoming';

	const ValidationRulesConfigVarName = 'validation';

	const DefaultTabName = 'Root.Main';

	const Arity = 0;

	// Zend_Locale_Format compatible format string, if blank then default for locale is used
	private static $time_field_format = '';

	private static $cms_tab_name = '';

	// only show this field if the user is an admin
	private static $admin_only = false;

	// default show as no ShowAsABCFlag set
	private static $show_as = 0;

	private static $validation = [
		'min'   => null,
		'max'   => null,
		'regex' => null,
	];

	/**
	 * If we use invocation we can type-cast the result to a ModularModel
	 *
	 * @return Model
	 */
	public function __invoke() {
		return $this->owner;
	}

	/**
	 * Used to generate e.g. relationships, override Arity constant in derived classes.
	 *
	 * @return int
	 */
	public static function arity() {
		return static::Arity;
	}

	/**
	 * Return the schema of the remote field, e.g. 'Varchar(32)', 'Enum("a,b,c")' or 'Member'. This is set
	 * in a Type, e.g. StringType if using modular-types or should be set in the concrete field implementation.
	 * @return string
	 */
	public static function schema() {
		return static::Schema;
	}

	/**
	 * a validation.min of null is not required, otherwise required if validation.min !== 0
	 *
	 * @return bool
	 */
	public static function required() {
		return is_null(static::min()) ? false : (static::min() !== 0);
	}

	public static function min() {
		return static::get_config_setting('validation', 'min');
	}

	public static function max() {
		return static::get_config_setting('validation', 'max');
	}

	public static function regex() {
		return static::get_config_setting('validation', 'regex');
	}

	/**
	 * @param mixed|null $set if provided sets the value on the model for SingleFieldValue and return this, otherwise
	 *                        returns the models SingleFieldValue.
	 * @return mixed
	 * @throws Exception if Name constant is not set via late static binding
	 * @getter-setter
	 * @fluent-setter
	 */
	public function singleFieldValue($set = null) {
		$name = static::field_name('');
		if (!$name) {
			throw new Exception("Called singleFieldValue() with no Name set");
		}
		if (func_num_args()) {
			$this()->{$name} = $set;

			return $this;
		} else {
			return $this()->{$name};
		}
	}

	/**
	 * Override in concrete classes to provide an array of fields which this extension adds.
	 *
	 * If Name and Schema constants are set then will scaffold a suitable
	 * for field for them, however complex fields requiring drop-down lists etc won't be populated.
	 *
	 * @param $mode
	 * @return array
	 */
	public function cmsFields($mode = null) {
		$fields = [];

		if (static::field_name() && static::schema()) {
			if ($dbField = $this()->dbObject(static::field_name())) {
				if ($formField = $dbField->scaffoldFormField()) {
					$fields[ static::field_name() ] = $formField;
				}
			}
		}

		return $fields;
	}

	/*
	 * Make the value of this field available in onAfterWrite as it was before any updates
	 */
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		$fieldName = static::field_name();

		if ($this()->isChanged($fieldName)) {
			if ($previousFieldName = static::previous_value_field_name()) {
				$changed = $this()->getChangedFields();
				if (array_key_exists($fieldName, $changed)) {
					if (array_key_exists('before', $changed[ $fieldName ])) {
						$this()->{$previousFieldName} = $changed[ $fieldName ]['before'];
					}
				}
			}
		}
	}

	/**
	 * Returns the value of a field before any updates to it.
	 *
	 * @param null $previousValue
	 * @return bool
	 */
	public function previousValue(&$previousValue = null) {
		$previousFieldName = static::previous_value_field_name();
		if (array_key_exists($previousFieldName, $this())) {
			$previousValue = $this()->{$previousFieldName};

			return true;
		}

		return false;
	}

	/**
	 * Return the name (path) of the tab in the cms this model's fields should show under from
	 * config.cms_tab_name in:
	 *
	 * this extension or if not set from
	 * the extended model or if not set
	 * then self.DefaultTabName.
	 *
	 * @return string
	 */
	protected function cmsTab() {
		return $this->config()->get('cms_tab_name')
			?: static::DefaultTabName;
	}

	/**
	 * @param int $testBits optional also test these bits are set and return true if all are set.
	 * @return int|bool
	 */
	protected function showAs($testBits = null) {
		$showAs = static::config()->get('show_as');
		if (!is_null($testBits)) {
			$showAs = $this->testbits($showAs, $testBits);
		}

		return $showAs;
	}

	/**
	 * @param string $suffix appended if supplied
	 * @return string
	 */
	public static function field_name($suffix = '') {
		return static::Name ? (static::Name . $suffix) : '';
	}

	public static function readonly_field_name($suffix = 'RO') {
		return static::field_name($suffix);
	}

	protected static function previous_value_field_name() {
		return static::PreviousFieldNamePrefix . static::field_name();
	}

	/**
	 * If static.Name && static.Schema are set add them to db array.
	 *
	 * @param null $class
	 * @param null $extension
	 * @return mixed
	 * @throws \Modular\Exceptions\Exception
	 */
	public function extraStatics($class = null, $extension = null) {
		$parent = parent::extraStatics($class, $extension) ?: [];

		if ($fieldName = static::field_name()) {
			$fieldSchema = isset($parent[ $fieldName ]) ? $parent[ $fieldName ] : static::schema();
		} else {
			$fieldSchema = static::schema();
		}
		$statics = [];

		// we want neither or both of name and schema
		if (in_array(
			count(
				array_filter(
					[
						$fieldName,
						$fieldSchema,
					]
				)
			), [
				0,
				2,
			]
		)
		) {
			$statics = array_merge_recursive(
				$parent,
				($fieldName && $fieldSchema)
					? ['db' => [$fieldName => $fieldSchema]]
					: []
			);
		}
		return $statics;

	}

	/**
	 * Update summary fields to use Label from localisation yml if it exists.
	 *
	 * @param array $fields
	 */
	public function updateSummaryFields(&$fields) {
		if (static::field_name()) {
			$fields[ static::field_name() ] = $this->fieldDecoration(
				static::field_name(),
				'Label',
				isset($fields[ static::field_name() ])
					? $fields[ static::field_name() ]
					: static::field_name()
			);
		}
	}

	/**
	 * By default checks the config.admin_only and admin permissions to see if field should be shown.
	 */
	public function shouldShow($mode = HasMode::DefaultMode) {
		return $this()->config()->get('admin_only') ? \Permission::check('ADMIN') : true;
	}

	/**
	 * Update form fields to have:
	 *  label, guide and description from lang.yml
	 *  minlength, maxlength and pattern from config.validation
	 *
	 */
	public function updateCMSFields(FieldList $fields) {
		if ($this->shouldShow()) {
			return;
		}
		$allFieldsConstraints = $this->config()->get(static::ValidationRulesConfigVarName) ?: [];

		$controller = Controller::curr();
		if ($controller->hasExtension('HasMode')) {
			$mode = $controller->getMode();
		} else {
			$mode = '';
		}

		if ($cmsFields = $this->cmsFields(HasMode::DefaultMode, $mode)) {
			/** @var FormField $field */
			foreach ($cmsFields as $field) {
				$fieldName = $field->getName();

				if ($fieldName) {
					// remove any existing field with this name already added e.g. by cms scaffolding.
					$fields->removeByName($fieldName);

					$this->addHTMLAttributes($field);

					$this->setFieldDecorations($field);

				}
				// add any extra constraints, display-logic etc on a per-field basis
				$this()->extend('customFieldConstraints', $field, $allFieldsConstraints);
			}
			$fields->addFieldsToTab(
				$this->cmsTab(),
				$cmsFields
			);
		}

	}

	/**
	 * Return a map of fieldname => value for data relevant to only this extension.
	 *
	 * @return array
	 */
	public function extendedFieldData() {
		$fieldNames = $this->extendedFieldNames();

		return array_intersect_key(
			$this()->toMap(),
			array_flip($fieldNames)
		);
	}

	/**
	 * Returns a numerically keyed map of field names relevant to this extension.
	 *
	 * @return array
	 */
	public function extendedFieldNames() {
		$fields = $this->cmsFields(HasMode::DefaultMode);

		$fieldNames = array_map(
			function ($field) {
				return $field->getName();
			},
			$fields
		);

		return $fieldNames;
	}

	/**
	 * Add any additional constraints, display_logic logic etc, this is called by extension on the extended model.
	 *
	 * TODO rename to extendedFieldConstraints
	 *
	 * @param \FormField $field
	 * @param array      $allFieldConstraints
	 */
	public function customFieldConstraints(FormField $field, array $allFieldConstraints) {
		// default does nothing, this is mainly here for the method template when deriving classes
	}

	/**
	 * Set field decorations, e.g. label, guide information etc
	 *
	 * @param \FormField $field
	 */
	protected function setFieldDecorations(FormField $field) {
		$fieldName = $field->getName();

		$field->setTitle(
			$this->fieldDecoration($fieldName, "Label", $field->Title(), [], $field)
		);
		$guide = $this->fieldDecoration($fieldName, "Guide", '', [], $field);

		$field->setRightTitle(
			$guide
		);
		if ($field instanceof \CheckboxField) {
			$field->setDescription($guide);
		}
	}

	/**
	 * Add any attributes, e.g. html5 validation, placeholder
	 *
	 * @param \FormField $field
	 */
	protected function addHTMLAttributes(FormField $field) {
		$fieldName = $field->getName();

		$field->setAttribute('placeholder', $this->fieldDecoration($fieldName, "Placeholder", '', [], $field));

		if (isset($allFieldsConstraints[ $fieldName ])) {
			// add html5 validation attributes
			list($minlength, $maxlength, $pattern) = $allFieldsConstraints[ $fieldName ];

			if (!is_null($minlength)) {
				$field->setAttribute('minlength', $minlength);
			}
			if (!is_null($maxlength)) {
				$field->setAttribute('maxlength', $maxlength);
			}
			if (!is_null($pattern)) {
				$field->setAttribute('pattern', $pattern);
			}
		}
	}

	/**
	 * Validates fields according to their validation rules, specifically
	 *
	 * @param \ValidationResult $result
	 * @return array of messages added to result object
	 * @throws \ValidationException
	 */
	public function validate(ValidationResult $result) {
		$this()->extend('onBeforeValidate', $result);

		$messages = [];
		$cmsFields = $this->cmsFields(HasMode::DefaultMode);

		if ($cmsFields) {

			// if one is defined all need to be defined
			/** @var FormField $field */
			foreach ($cmsFields as $field) {
				$fieldName = $field->getName();
				$fieldConstraints = $this->fieldConstraints(
					$fieldName, [
						0,
						0,
						'',
					]
				);

				//if there are no validation rules for this field, or they are 'empty' rules move onto the next one
				if (!$fieldConstraints
					|| $fieldConstraints == [
						0,
						0,
						'',
					]
				) {
					continue;
				}

				// deconstruct the constraints
				list($minlength, $maxlength, $pattern) = $fieldConstraints;

				$lengthType = null;
				$length = 0;

				if ($this()->hasField($fieldName . 'ID')) {
					// get the title before we append ID
					$lengthType = $field->Title() ?: $fieldName;

					$fieldName = $fieldName . 'ID';
					$length = $this()->$fieldName ? 1 : 0;

				} elseif ($this()->hasMethod($fieldName)) {
					if ($value = $this()->$fieldName()) {
						if ($value instanceof SS_List) {
							$length = $value->count();
							$lengthType = $this()->i18n_plural_name();
						}
					}
				} elseif ((substr($fieldName, -2, 2) == 'ID') && $this()->hasMethod(substr($fieldName, -2 - 2))) {
					$length = $this()->$fieldName();
					$lengthType = $this()->i18n_singular_name();
				}
				if (is_null($lengthType)) {
					$value = $this()->$fieldName;

					if (is_array($value)) {
						$length = count($value);
						$lengthType = 'choice';
					} else {
						// need to strip tags to get a realistic length on html fields, just leave white-space out of count
						$length = $this->valueLength($value);
						$lengthType = 'letter';
					}
				}

				if ($pattern) {
					// set start and end pattern of '~' so we can use slashes in the config file
					// and make regexps just a we bit more friendly.
					$pattern = '~' . trim($pattern, '/~') . '~';

					if (false === preg_match($pattern, $value)) {
						// add pattern error message to $messages
						$messages[] = $this->fieldDecoration(
							$fieldName,
							"Format", "be in format {pattern}",
							[
								'pattern' => $pattern,
							],
							$field
						);
					}
				}

				//validate that value falls between the min and max length
				$lengthMessage = '';
				if ($minlength != $maxlength) {
					if ($minlength && ($length < $minlength)) {
						if ($minlength == 1) {
							$lengthMessage = 'be provided';
						} else {
							$lengthMessage = "have at least {minlength} $lengthType" . ($minlength > 1 ? 's' : '');
						}
					}
					if ($maxlength && ($length > $maxlength)) {
						$lengthMessage = "have at most {maxlength} $lengthType" . ($maxlength > 1 ? 's' : '');
					}
				} else {
					if ($minlength && ($length < $minlength)) {
						if ($minlength == 1) {
							$lengthMessage = 'be provided';
						} else {
							$lengthMessage = "{minlength} $lengthType" . ($minlength > 1 ? 's' : '');;
						}
					}
				}
				if ($lengthMessage) {
					$messages[] = $this->fieldDecoration(
						$fieldName, "Length", $lengthMessage,
						[
							'minlength' => $minlength,
							'maxlength' => $maxlength,
							'pattern'   => $pattern,
						],
						$field
					);
				}

				//if there were any error messages, set the error result and throw exception
				if ($messages) {
					$message = $this->fieldDecoration(
						$fieldName,
						"Label",
						"{label} should " . implode(' and ', $messages),
						[
							'label' => $field->Title() ?: $fieldName,
						]
					);

					$result->error($message);

					throw new ValidationException($result, $message);
				}
			}
		}
	}

	/**
	 * Return a stripped out length of a value excluding whitespace and tags.
	 *
	 * @param $value
	 * @return int
	 */
	protected function valueLength($value) {
		return strlen(strip_tags(preg_replace('/\s+/', '', $value)));
	}

	/**
	 * If a fieldName is a relationship name then returns a nice label for the remote class name, otherwise empty array.
	 * Only handles many_many at the moment.
	 *
	 * TODO: handle has_many and has_one
	 *
	 * @param $fieldName
	 * @return array of [singular, plural] names or empty array if not found.
	 */
	protected function labelsForRelatedClass($fieldName) {
		if ($manyMany = $this()->manyManyComponent($fieldName)) {
			while ($schema = array_shift($manyMany)) {

				if ($schema == $this()->class) {
					$singleton = singleton($schema);

					return [
						$singleton->i18n_singular_name(),
						$singleton->i18n_plural_name(),
					];
				}
				// shift again as manyManyComponent returns interleaved array of schema/class
				array_shift($manyMany);
			}
		} elseif ($hasMany = $this()->hasManyComponent($fieldName)) {
			// TODO: handle has_many
			// xdebug_break();
		} elseif ($hasOne = $this()->hasOneComponent($fieldName)) {
			// TODO: handle has_one
			// xdebug_break();
		}

		return [];
	}

	/**
	 * Configure date fields to be in various states as per parameter options.
	 *
	 * @param \DateField $field
	 * @param bool       $showMultipleFields
	 * @return \DateField
	 */
	protected function configureDateField(DateField $field, $showMultipleFields = true) {
		if ($showMultipleFields) {
			$field->setConfig('dmyfields', true)
				->setConfig('dmyseparator', ' / ')// set the separator
				->setConfig('dmyplaceholders', 'true'); // enable HTML 5 Placeholders
		}

		return $field;
	}

	/**
	 * @param \TimeField $field
	 * @param bool       $showMultipleFields
	 * @return TimeField
	 */
	protected function configureTimeField(TimeField $field, $showMultipleFields = true) {
		if ($showMultipleFields) {
			// if not set then the default will be used for the locale
			if ($format = $this->config()->get('time_field_format')) {
				$field->setConfig('timeformat', $format);
			}
		}

		return $field;
	}

	/**
	 * Configures the Date and Time fields in the wrapping DatetimeField.
	 *
	 * @param \DatetimeField $field
	 * @param bool           $showMultipleFields
	 * @return DatetimeField
	 */
	protected function configureDateTimeField(DatetimeField $field, $showMultipleFields = true) {
		if ($showMultipleFields) {
			$this->configureDateField($field->getDateField(), $showMultipleFields);
			$this->configureTimeField($field->getTimeField(), $showMultipleFields);
		}

		return $field;
	}

	/**
	 * Returns an array of field constraints for the named field as defined in config.validation, so
	 * [minlength, maxlength, pattern] or defaults (which is no checks) if not found.
	 *
	 * @param string $fieldName
	 * @param array  $defaults to use if not found in config for that field = no validation performed
	 * @return array
	 */
	public function fieldConstraints($fieldName, array $defaults = [
		0,
		0,
		'',
	]) {
		$allFieldsConstraints = array_merge(
			$this->config()->get(static::ValidationRulesConfigVarName) ?: [],
			$this()->config()->get(static::ValidationRulesConfigVarName) ?: []
		);
		$constraints = $defaults;;

		foreach ([
			         $fieldName,
			         $fieldName . 'ID',
		         ] as $name) {
			if (isset($allFieldsConstraints[ $name ])) {
				if (is_bool($allFieldsConstraints[ $name ])) {
					// use the boolean as the min length, could be 0 or 1 which is enough
					$constraints = [
							(int) $allFieldsConstraints[ $name ],
							0,
							'',
						] + $defaults;
				} else {
					// presume it's an array or something else we handle
					$constraints = $allFieldsConstraints[ $name ];
				}
				break;
			}
		}

		return $constraints;
	}

	protected function saveMasterHint() {
		return new LiteralField(
			static::field_name() . 'Hint',
			$this->fieldDecoration(
				static::field_name(),
				'SaveMasterHint',
				"<b>Please save the master first</b>"
			)
		);
	}
}
