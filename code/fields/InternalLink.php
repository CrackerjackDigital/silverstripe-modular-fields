<?php

namespace Modular\Fields;

use DisplayLogicWrapper;
use SiteTree;
use TreeDropdownField;

/**
 * InternalLink is a link to a page on the site
 *
 * @package Modular\Fields
 */
class InternalLink extends RefOneField {
	const InternalLinkOption    = 'InternalLink';
	const Name                  = 'InternalLink';
	const Schema                = SiteTree::class;

	public function cmsField( $mode = null ) {
		return [
			( new DisplayLogicWrapper(
				new TreeDropdownField( self::field_name(), 'Link to', SiteTree::class)
			) )->setName( self::field_name())->setID( self::field_name()),
		];
	}
}