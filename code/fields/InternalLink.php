<?php
namespace Modular\Fields;

use DisplayLogicWrapper;
use TreeDropdownField;

class InternalLink extends \Modular\TypedField {
	const InternalLinkOption    = 'InternalLink';
	const InternalLinkFieldName = 'InternalLinkID';
	const Name      = 'InternalLink';

	private static $has_one = [
		self::Name => 'SiteTree',
	];

	public function cmsFields($mode) {
		return [
			(new DisplayLogicWrapper(
				new TreeDropdownField(self::InternalLinkFieldName, 'Link to', 'SiteTree')
			))->setName(self::InternalLinkFieldName)->setID(self::InternalLinkFieldName),
		];
	}
}