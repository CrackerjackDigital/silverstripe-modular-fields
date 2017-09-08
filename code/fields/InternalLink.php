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
	const InternalLinkFieldName = 'InternalLinkID';
	const Name                  = 'InternalLink';
	const Schema                = SiteTree::class;

	public function cmsField( $mode = null ) {
		return [
			( new DisplayLogicWrapper(
				new TreeDropdownField( self::InternalLinkFieldName, 'Link to', SiteTree::class)
			) )->setName( self::InternalLinkFieldName )->setID( self::InternalLinkFieldName ),
		];
	}
}