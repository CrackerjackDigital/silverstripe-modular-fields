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
	const Error         = 'Error';              // an error occurred processing

	private static $ready_states = [
		self::NotDetermined
	];

	private static $halt_states = [
		self::Success,
	    self::Failed,
	    self::Error
	];

	private static $options = [
		self::NotDetermined => [
			self::NotDetermined,                // e.g. back for another go or step, haven't found answer yet
			self::Determining,                  // e.g. running a task, downloading a resource etc
		    self::Error                         // e.g. tried to start a task but failed
		],
		self::Determining => [
			self::NotDetermined,                // e.g. I'll try again or finish off later
			self::Determining,                  // e.g. still running task, download etc
		    self::Success,                      // all done with a good result
		    self::Failed,                       // did what I should but not a good result
		    self::Error,                        // hard fail, can't retry
		],
		self::Success       => [
			self::NotDetermined                 // do it again
		],
		self::Failed        => [
			self::NotDetermined                 // retry
		],
	    self::Error         => []               // hard error, re-running as is won't ever get a reasonable outcome
	];


}