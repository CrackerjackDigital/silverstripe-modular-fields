<?php
namespace Modular\Fields;

use Modular\TypedField;
use Modular\Types\Time;
use TextField;

/**
 * Field representing a duration in seconds and parts thereof as a float, whole seconds being the integer part.
 *
 * @package Modular\Fields
 */
class Duration extends TypedField implements Time {
	const Name = 'Duration';
	// const Schema = 'Float';

	public function cmsFields($mode) {
		if ($this->showAs(static::ShowAsReadOnlyFlag)) {
			$field = new \ReadonlyField(static::readonly_field_name());
		} else {
			$field = new \NumericField(static::field_name());
		}
		return [
			$field
		];
	}

	/**
	 * Compare provided date with internal date (either AuthTokenUsed or Created) + duration and return an int as follows:
	 *  -ve value:  internal date + duration is before provided date, so valid by x seconds/microseconds
	 *  0        :  dates are the same
	 *  +ve value:  internal date + duration is after provided date, so expired for x seconds/microseconds
	 *
	 * @param $withDate
	 * @return int
	 * @throws \Modular\Exceptions\Exception
	 */
	public function compare($withDate) {
		return $this->singleFieldValue() - $withDate;
	}

	/**
	 * Return true if model timestamp is before now (or provided date), false otherwise.
	 *
	 * @param null $testDate if not provided then current date/time/timestamp will be used
	 * @return bool
	 * @throws \Modular\Exceptions\Exception
	 */
	public function expired($testDate = null) {
		$testDate = is_null($testDate) ? $this->now() : $testDate;
		return $this->compare($testDate) > 0;
	}

	/**
	 * Opposite of expired.
	 * @param null $testDate
	 * @return bool
	 * @throws \Modular\Exceptions\Exception
	 */
	public function valid($testDate = null) {
		return !$this->expired($testDate);
	}

	/**
	 * Generate a new value which is 'now' so can be used in comparison, expiry checks etc
	 *
	 * @return mixed
	 */
	public function now() {
		return microtime(true);
	}
}
