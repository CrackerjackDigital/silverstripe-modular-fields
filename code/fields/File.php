<?php
namespace Modular\Fields;

use FormField;
use Modular\Field;
use Modular\Interfaces\Arities;
use Modular\Traits\upload;
use Modular\TypedField;
use Modular\Types\FileType;
use Modular\Types\RefOneType;
use Modular\Types\URNType;
use UploadField;

/**
 * File field represents an attached file (single), there is another in modular-relationships which uses the HasOne relationship for most functionality instead.
 *
 * @package Modular\Fields
 */
class File extends RefOneField implements FileType, RefOneType {
	use upload;

	const Name                    = 'File';
	const DefaultUploadFolderName = 'files';
	const RelatedKeyField         = 'ID';
	const RelatedDisplayField     = 'Title';

	// if an array then file extensions, if a string then a category e.g. 'video'

	private static $allowed_files = 'download';

	private static $tab_name = 'Root.Files';

	// folder directly under '/assets'
	private static $base_upload_folder = '';

	// this will be appended to 'base_upload_folder'
	private static $upload_folder = self::DefaultUploadFolderName;


	/**
	 * Add upload field.
	 * @param null $mode
	 *
	 * @return array
	 */
	public function cmsField( $mode = null ) {
		return [
			$this->makeUploadField( static::field_name() ),
		];
	}

	public static function allowed_files() {
		return 'allowed_files';
	}

	public function customFieldConstraints( FormField $field, array $allFieldConstraints ) {
		$fieldName = $field->getName();
		/** @var UploadField $field */
		if ( $fieldName == static::field_name() ) {
			$this->configureUploadField( $field, static::allowed_files() );
		}
	}

	/**
	 * If file is versioned we need to publish it also.
	 */
	public function onAfterPublish() {
		/** @var \File|\Versioned $file */
		if ( $file = $this()->{static::relationship_name()}() ) {
			if ( $file->hasExtension( 'Versioned' ) ) {
				$file->publish( 'Stage', 'Live', false );
			}
		}
	}

	/**
	 * Return map of key field => title for the drop down where the relationship target can be chosen.
	 *
	 * @return array
	 */
	public static function options() {
		return \DataObject::get( static::schema() )->map( static::RelatedKeyField, static::RelatedDisplayField )->toArray();
	}


}
