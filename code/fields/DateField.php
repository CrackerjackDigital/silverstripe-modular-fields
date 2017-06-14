<?php
namespace Modular\Fields;

use DateField as DField;
use DatetimeField as DTField;
use Modular\TypedField;
use Modular\Types\DateTimeType;
use TimeField as TField;

/**
 * A EventDate field which is distinct from the SilverStripe 'Created' field.
 */
abstract class DateTimeField extends TypedField implements DateTimeType {
	// override for field name in implementation class
	const Name = '';

	// show time field or just the date field?
	const ShowTimeField = false;
	// show Year, Month, Day, Hours, Minutes as separated fields, one per unit
	const ShowSeparateFields = true;

	const DateRequired = true;
	const TimeRequired = false;

	const DefaultNow = true;

	/**
	 * Add validation based on self.DateRequired
	 * @param null $class
	 * @param null $extension
	 *
	 * @return array
	 */
	public function extraStatics($class = null, $extension = null) {
		return array_merge_recursive(
			parent::extraStatics($class, $extension) ?: [],
			[
				'validation' => [
					static::field_name() => static::DateRequired,
				]
			]
		);
	}

	/**
	 * Convenience method returns a MySQL compatible date time for 'now'
	 * @return false|string
	 */
	public static function now() {
		return date('Y-m-d H:i:s');
	}

	/**
	 * Hack to get multiple year, month, day values into the models date field if present as an array in the post data.
	 * Sets to now() if new record, value is not set and self.DefaultNow is true and either Date or Time is required
	 *
	 * @return bool|void
	 */
	public function onBeforeValidate() {
		$postVars = \Controller::curr()->getRequest()->postVars();
		if (isset($postVars[ static::field_name() ]) && is_array($postVars[ static::field_name() ])) {
			$date = $postVars[ static::field_name() ];

			if (count(array_filter($date)) == 3) {
				$this()->{static::field_name()} = implode('-', [$date['year'], $date['month'], $date['day']]);
			}
		}
		if ( static::DefaultNow && ( static::DateRequired || static::TimeRequired )
		     && !$this()->{static::Name} && ! $this()->isInDB() )
		{
			$this()->{static::Name} = static::now();
		}
	}

	/**
	 * Skip validation for Pages if not already saved so we can create new pages with DateFields as CMS saves early.
	 *
	 * @param \ValidationResult $result
	 * @throws \ValidationException
	 * @return null
	 */
	public function validate(\ValidationResult $result) {
		if ($this() instanceof \SiteTree) {
			if (!$this()->isInDB()) {
				return null;
			}
		}
		parent::validate($result);
	}

	/**
	 * Adds label for this field to summary fields.
	 * @param array $fields
	 */
	public function updateSummaryFields(&$fields) {
		$fields[ static::field_name() ] = $this->fieldDecoration(static::field_name());
	}

	/**
	 * Returns fields for entering date and time. NB injector has overridden the TimeField to be CERATimeField to
	 * fix a problem saving DatatimeField with no date.
	 *
	 * @param $mode
	 * @return array
	 */
	public function cmsField($mode = null) {
		if (static::ShowTimeField) {
			return [
				DTField::create(
					static::field_name()
				),
			];
		} else {
			return [
				DField::create(
					static::field_name()
				),
			];
		}
	}

	/**
	 * Configures the date field.
	 *
	 * @param \FormField $field
	 * @param array      $allFieldConstraints
	 *
	 * @throws \InvalidArgumentException
	 */
	public function customFieldConstraints(\FormField $field, array $allFieldConstraints) {
		/** @var DField $field */
		if ($field->getName() == static::field_name()) {
			if ($field instanceof DTField) {
				$this->configureDateTimeField($field, static::ShowSeparateFields);
			} else {
				$this->configureDateField($field, static::ShowSeparateFields);
			}
		}
	}

	/**
	 * Configure date fields to be in various states as per parameter options.
	 *
	 * @param DField $field
	 * @param bool   $showMultipleFields
	 *
	 * @return \DateField|void
	 * @throws \InvalidArgumentException
	 */
	protected function configureDateField(DField $field, $showMultipleFields = true) {
		if ($showMultipleFields) {
			$field->setConfig('dmyfields', true)
				->setConfig('dmyseparator', ' / ')// set the separator
				->setConfig('dmyplaceholders', 'true'); // enable HTML 5 Placeholders
		}
	}

	/**
	 * @param TField $field
	 * @param bool   $showMultipleFields
	 *
	 * @return \TimeField|void
	 */
	protected function configureTimeField(TField $field, $showMultipleFields = true) {
		if ($showMultipleFields) {
			// if not set then the default will be used for the locale
			if ($format = $this->config()->get('time_field_format')) {
				$field->setConfig('timeformat', $format);
			}
		}
	}

	/**
	 * Configures the Date and Time fields in the wrapping DatetimeField.
	 *
	 * @param DTField $field
	 * @param bool    $showMultipleFields
	 *
	 * @return \DatetimeField|void
	 * @throws \InvalidArgumentException
	 */
	protected function configureDateTimeField(DTField $field, $showMultipleFields = true) {
		if ($showMultipleFields) {
			$this->configureDateField($field->getDateField(), $showMultipleFields);
			$this->configureTimeField($field->getTimeField(), $showMultipleFields);
		}
	}
}