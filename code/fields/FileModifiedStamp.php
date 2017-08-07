<?php
namespace Modular\Fields;

/**
 * Add to a File to track the last modified time (in unix time) of a file e.g. from filemtime() call.
 */

use Modular\Traits\file_changed;
use Modular\Traits\md5;
use Modular\TypedField;
use Modular\Types\IntType;

class FileModifiedStamp extends TypedField implements IntType {
	use file_changed;
	use md5;            // for hash_file

	const Name = 'FileModifiedStamp';

	private static $admin_only = true;

	public function hashFile($fileName = '') {
		return static::hash_file( $fileName ?: $this()->Filename);
	}

	public function onBeforeWrite() {
		parent::onBeforeWrite();

		if ( $this->fileChanged(true) ) {
			$fullPathName = \Director::getAbsFile( $this()->Filename );

			if ( file_exists( $fullPathName ) ) {
				$this()->{static::Name} = filemtime( $fullPathName );
			}
		}
	}
}