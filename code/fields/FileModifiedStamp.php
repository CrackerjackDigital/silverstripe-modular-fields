<?php

namespace Modular\Fields;

use Modular\Traits\file_changed;
use Modular\TypedField;
use Modular\Types\IntType;

class FileModifiedStamp extends TypedField implements IntType {
	use file_changed;

	const Name = 'FileModifiedStamp';

	public function onBeforeWrite() {
		parent::onBeforeWrite();

		if ( $this->fileChanged() ) {
			$fullPathName = \Director::getAbsFile( $this()->Filename );

			if ( file_exists( $fullPathName ) ) {
				$this()->{static::Name} = filemtime( $fullPathName );
			}
		}
	}
}