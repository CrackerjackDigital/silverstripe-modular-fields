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
class File extends TypedField implements FileType, RefOneType {
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
	 * Add has_one relationships to related class.
	 *
	 * @param null $class
	 * @param null $extension
	 *
	 * @return mixed
	 */
	public function extraStatics( $class = null, $extension = null ) {
		return array_merge_recursive(
			parent::extraStatics( $class, $extension ) ?: [],
			[
				'has_one' => [
					static::relationship_name() => static::related_class_name(),
				],
			]
		);
	}

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
	 * has_one relationships need an 'ID' appended to the relationship name to make the field name
	 *
	 * @param string $suffix defaults to 'ID'
	 *
	 * @return string
	 */
	public static function related_field_name( $suffix = 'ID' ) {
		return static::field_name() . $suffix;
	}

	/**
	 * Return unadorned has_one related class name.
	 *
	 * @return string
	 */
	public static function related_class_name() {
		return static::schema();
	}

	/**
	 * Returns the Name for this field if set, optionally appended with the fieldName as for a relationship.
	 *
	 * @param string $fieldName if supplied will be added on to Name with a '.' prefix
	 *
	 * @return string
	 */
	public static function relationship_name( $fieldName = '' ) {
		return static::field_name() ? ( static::field_name() . ( $fieldName ? ".$fieldName" : '' ) ) : '';
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
