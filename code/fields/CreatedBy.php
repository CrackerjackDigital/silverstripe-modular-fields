<?php
namespace Modular\Fields;

use Member;

class CreatedBy extends RefOneField {
	const Name = 'CreatedBy';
	const Schema = Member::class;

	public function onBeforeWrite() {
		parent::onBeforeWrite();
		$this()->{static::field_name(static::IDFieldSuffix)} = Member::currentUserID();
	}
}