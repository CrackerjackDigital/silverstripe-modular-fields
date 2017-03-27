<?php
namespace Modular\Fields;

/**
 * Outcome represents a 'Success', 'Failed', 'NotDetermined', 'Determining' state engine, e.g. for a LogEntry which records the outcome of a process.
 *
 * @package Modular\Fields
 */
class Outcome extends StateEngineField {
	const Name          = 'Outcome';

	const NotDetermined = 'NotDetermined';      // default state no outcome yet
	const Determining   = 'Determining';        // doing something to get an outcome
	const Success       = 'Success';            // outcome was success
	const Failed        = 'Failed';             // outcome was failure

	private static $options = [
		self::NotDetermined => [
			self::NotDetermined,
			self::Determining,
		],
		self::Determining => [
			self::NotDetermined,
			self::Determining,
		    self::Success,
		    self::Failed
		],
		self::Success       => [],
		self::Failed        => [],
	];

}