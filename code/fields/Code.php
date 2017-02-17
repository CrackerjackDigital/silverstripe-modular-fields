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

	const SystemCode        = '_SYS_';
	const SingleFieldName   = 'Code';
	const SingleFieldSchema = 'Varchar(5)';

	public function cmsFields($mode) {
		if ($this()->isInDB()) {
			return [
				new ReadonlyField(self::SingleFieldName . 'RO', 'Unique Code', $this()->{self::SingleFieldName}),
				new HiddenField(self::SingleFieldName),
			];
		} else {
			return [
				new TextField(self::SingleFieldName),
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
			$query->addWhere(self::SingleFieldName . " != '" . self::SystemCode . "'");
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