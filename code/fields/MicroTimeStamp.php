<?php
namespace Modular\Fields;

class MicroTimeStamp extends TimeStamp {
	use \Modular\Traits\microtimestamp {
		microtimestamp as generator;
	}
	const Name   = 'MicroTimeStamp';
	// const Schema = 'Float';

}
