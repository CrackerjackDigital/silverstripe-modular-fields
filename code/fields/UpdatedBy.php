<?php
namespace Modular\Fields;

use Member;

class UpdatedBy extends RefOneField {
	const Name = 'UpdatedBy';
	const Schema = Member::class;

	public function onBeforeWrite() {
		parent::onBeforeWrite();
		$this()->{static::field_name( self::IDFieldSuffix )} = Member::currentUserID();
	}
}