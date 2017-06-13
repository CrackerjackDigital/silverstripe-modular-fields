<?php

namespace Modular\Fields;

use Modular\Traits\file_changed;
use Modular\Traits\md5;
use Modular\TypedField;
use Modular\Types\StringType32;

class FileContentHash extends TypedField implements StringType32 {
	use md5, file_changed;

	const Name = 'FileContentHash';

	public function onBeforeWrite() {
		parent::onBeforeWrite();

		if ( $this->fileChanged(true) ) {
			$fullPathName = \Director::getAbsFile( $this()->Filename );

			if ( file_exists( $fullPathName ) ) {
				$this()->{static::Name} = static::hash_file( $fullPathName );
			}
		}
	}
}