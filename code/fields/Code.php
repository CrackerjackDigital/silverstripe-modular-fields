<?php
namespace Modular\Fields;

use Config;
use HiddenField;
use Modular\Traits\enabler;
use Modular\Model;
use Permission;
use ReadonlyField;
use SQLQuery;
use TextField;
use ValidationException;
use ValidationResult;

/**
 * Adds a 5-letter 'Code' field to the extended model and makes it readonly in CMS,
 * and adds ability for a code of SYSTM to be filtered out via augmentSQL extension call.
 */
class Code extends UniqueField {
	use enabler;

	const SystemCode = '_SYS_';
	const Name       = 'Code';
	// const Schema     = 'Varchar(5)';

	public function cmsFields($mode) {
		if ($this()->isInDB()) {
			return [
				new ReadonlyField(self::Name . 'RO', 'Unique Code', $this()->{self::Name}),
				new HiddenField(self::Name),
			];
		} else {
			return [
				new TextField(self::Name),
			];
		}
	}

	/**
	 * Don't show TaxonomyTerms with code of SYSTM unless you're an Admin or config.augment_enabled = false
	 *
	 * @param \SQLQuery $query
	 */
	public function augmentSQL(SQLQuery &$query) {
		parent::augmentSQL($query);

		if ($this->enabled() && !Permission::check('ADMIN')) {
			$query->addWhere(self::Name . " != '" . self::SystemCode . "'");
		}
	}

	/**
	 * Return passed string as an array of codes, the passed string may be an array already, a single code or a csv list of codes.
	 *
	 * @param array|string $codes
	 * @return array
	 */
	public static function parse_codes($codes) {
		if (!is_array($codes)) {
			$codes = array_filter(explode(',', $codes));
		}
		return $codes;
	}

}