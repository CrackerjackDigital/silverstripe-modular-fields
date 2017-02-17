<?php
namespace Modular\Fields;

use Modular\Field;
use Modular\Interfaces\ValueGenerator;
use Modular\Traits\generator;
use Modular\Types\NumericType;

class TimeStamp extends Field implements ValueGenerator, NumericType {
	use generator;
	use \Modular\Traits\timestamp {
		timestamp as generator;
	}
	const SingleFieldName   = 'TimeStamp';
	const SingleFieldSchema = 'Int';
	
	const ShowAsAgoFlag = 3;            // always read only
	
	private static $generate_always = false;
	
	private static $generate_empty = true;
	
	private static $show_as = self::ShowAsAgoFlag;
	
	public function cmsFields($mode) {
		$value = round($this()->{static::single_field_name()});
		
		$dateTime = new \SS_Datetime();
		$dateTime->setValue($value);
		
		if ($this->showAs(static::ShowAsReadOnlyFlag)) {
			$field = new \ReadonlyField(static::readonly_field_name());
			
			if ($this->showAs(static::ShowAsAgoFlag)) {
				$field->setValue($dateTime->Ago(true));
			} else {
				$field->setValue($dateTime->Nice());
			}
		} else {
			$field = new \DatetimeField(static::single_field_name());
			$field->setValue($value);
			$this->configureDateTimeField($field, false);
		}
		
		return [
			$field,
		];
	}
	/**
	 * Return
	 *  -ve     value if internal date is less than comparison date (so expired)
	 *  0       if the dates are equal
	 *  +ve     value if internal date is after the comparison date (so not expired)
	 *
	 * @param $timeStamp
	 * @return Int
	 * @throws \Modular\Exceptions\Exception
	 */
	public function compare($timeStamp) {
		return $timeStamp - $this->singleFieldValue();
	}
	
	
	/**
	 * Generate a new value which is 'now' so can be used in comparison, expiry checks etc
	 *
	 * @return mixed
	 */
	public function now() {
		return $this->generator();
	}
	
	/**
	 * Return true if model timestamp is before now (or provided date), false otherwise.
	 *
	 * @param null $testDate if not provided then current date/time/timestamp will be used
	 * @return bool
	 * @throws \Modular\Exceptions\Exception
	 */
	public function expired($testDate = null) {
		$testDate = is_null($testDate) ? $this->generator() : $testDate;
		
		return $this->compare($testDate) >= 0;
	}
	
	/**
	 * Return oppsite to expired.
	 * @param null $testDate
	 * @return bool
	 * @throws \Modular\Exceptions\Exception
	 */
	public function valid($testDate = null) {
		return !$this->expired($testDate);
	}
	
	
	/**
	 * If empty then set a value. If config.generate_always then always set new value.
	 */
	public function onBeforeWrite() {
		if ($this->shouldGenerate()) {
			$this()->{static::SingleFieldName} = $this->now();
		}
	}
}