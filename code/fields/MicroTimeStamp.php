<?php
namespace Modular\Fields;

class MicroTimeStamp extends TimeStamp {
	use \Modular\Traits\microtimestamp {
		microtimestamp as generator;
	}
	const SingleFieldName   = 'MicroTimeStamp';
	const SingleFieldSchema = 'Float';
	
}
