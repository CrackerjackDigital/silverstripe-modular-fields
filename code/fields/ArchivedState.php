<?php

namespace Modular\Fields;

use \Permission;
use Modular\Traits\enabler;
use SQLQuery;

/**
 * Adds an archival state tracking flag field to extended model. Models with this set to
 * 'Archived' will be filtered out unless the current member has ADMIN permissions or the CAN_VIEW_Archived permission.
 *
 * @package Modular\Fields
 */
class ArchivedState extends StateEngineField implements \PermissionProvider {
	use enabler;

	// name of field on extended model
	const Name = 'ArchivedState';

	// a Permission with this code will be added if it doesn't exist.
	const PermissionCode = 'CAN_VIEW_Archived_Models';

	// valid states for field
	const NeverArchived = 'Active';
	const Archived      = 'Archived';
	const Restored      = 'Restored';

	// enable or disable this extension filtering via augmentSQL
	private static $enabled = true;

	private static $options = [
		self::NeverArchived => [
			self::Archived,
		],
		self::Archived      => [
			self::Restored,
		],
		self::Restored      => [
			self::Archived,
		],
	];

	// no halt states can always transition
	private static $halt_states = [];

	// always able to transition
	private static $ready_states = [
		self::NeverArchived,
		self::Archived,
		self::Restored,
	];

	/**
	 * Add CAN_VIEW_Archived Permission if it doesn't exist.
	 */
	public function requireDefaultRecords() {
		parent::requireDefaultRecords();
		if ( static::enabled() ) {
			if ( 0 == Permission::get()->filter( 'Code', static::PermissionCode )->count() ) {
				Permission::create( [
					'Code' => static::PermissionCode,
				] )->write();
			}
		}
	}

	/**
	 * If we're not an admin and we're enabled then filter out models which are in 'Archived' state.
	 *
	 * @param \SQLQuery $query
	 */
	public function augmentSQL( SQLQuery &$query ) {
		parent::augmentSQL( $query );
		if ( static::enabled() ) {
			if ( ! Permission::check( [ 'ADMIN', static::PermissionCode ] ) ) {
				$query->addWhere( static::Name . " <> '" . static::Archived . "'");
			}
		}
	}

	/**
	 * Return a map of permission codes to add to the dropdown shown in the Security section of the CMS.
	 * array(
	 *   'CAN_VIEW_Archived_Models' => 'View Archived records',
	 * );
	 */
	public function providePermissions() {
		if ( static::enabled() ) {

			return [
				static::PermissionCode => _t(
					get_called_class() . '.Title',
					"Can view {Archived} records",
					[
						'Archived' => static::Archived,
					]
				),
			];
		}

		return [];
	}
}