<?php
namespace Modular\Fields;

use Modular\Fields\Flag;
use Modular\Traits\enabler;

/**
 * This extension provides a flag for data which may have 'System' significance and so
 * should generally not be returned for the user to view and/or choose, e.g. a SocialEdgeType
 * may only be useable by the system and not selectable in a dropdown.
 */
class SystemData extends Flag {
	use enabler;

	const Name= 'SystemFlag';

	// can be set to false if all values are required to be returned, e.g. when building the SocialEdgeType table
	// we need to be able to check for existing System records.
	private static $enabled = true;

	public function augmentSQL(\SQLQuery &$query) {
		if (self::enabled()) {
			$query->addWhere(self::field_name() . " = " . self::NoValue);
		}
		parent::augmentSQL($query);
	}
}