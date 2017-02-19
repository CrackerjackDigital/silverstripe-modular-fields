<?php
namespace Modular\Fields;

use ArrayList;
use FormField;
use Modular\Interfaces\Imagery;
use Modular\Types\ImageType;

/**
 * Image represents a single attached image, this is a relationship so use Name and RelationshipClassName
 * not Name and Schema.
 *
 * @package Modular\Fields
 */
class Image extends File implements Imagery, ImageType {
	const Name = 'Image';
	// const Schema = 'Image';

	private static $base_upload_folder = 'images';

	private static $allowed_files = 'image';

	/**
	 * Return a list with only item being the single related image.
	 *
	 * @return \ArrayList
	 */
	public function Images() {
		return new ArrayList(array_filter([$this->Image()]));
	}

	/**
	 * Return the single related image, shouldn't really get here as the extended model's field accessor should be called first.
	 *
	 * @return Image|null
	 */
	public function Image() {
		return $this()->{self::Name}();
	}

}