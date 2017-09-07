<?php

namespace Modular\Fields;

use FieldList;
use Modular\Traits\file_changed;
use Modular\Traits\md5;
use Modular\TypedField;
use Modular\Types\StringType32;

class FileContentHash extends TypedField implements StringType32 {
	use md5, file_changed;

	const Name = 'FileContentHash';

	private static $admin_only = true;

	public function onBeforeWrite() {
		parent::onBeforeWrite();

		if ( $this->fileChanged(true) ) {
			$fullPathName = \Director::getAbsFile( $this()->Filename );

			if ( file_exists( $fullPathName ) ) {
				$this()->{static::Name} = static::hash_file( $fullPathName );
			}
		}
	}

	public function updateCMSFields( FieldList $fields ) {
		parent::updateCMSFields( $fields );
		if ($this->owner->ID && \Permission::check('ADMIN') && $fields->hasTabSet()) {
			$fields->addFieldToTab(
				'Root.Admin',
				new \TextField( self::Name )
			);
		} else {
			$fields->removeByName( self::Name);
		}
	}
}