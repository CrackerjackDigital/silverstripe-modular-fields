<?php
namespace Modular\Fields;

abstract class UploadedFile extends \Modular\TypedField {
	const Name = '';
	const UploadFolderName = 'uploads';

	// has_one relationship goes on concrete class to pick up static Name, File model etc

	public function onAfterPublish() {
		/** @var \File|\Versioned $file */
		foreach ($this->{static::Name}() as $file) {
			if ($file && $file->hasExtension('Versioned')) {
				$file->publish('Stage', 'Live', false);
			}
		}
	}
}