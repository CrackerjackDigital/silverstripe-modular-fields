<?php
namespace Modular\Fields;

use Modular\Types\StringType;

class TemplateName extends \Modular\TypedField implements StringType {
	const Name   = 'TemplateName';
	// const Schema = 'Varchar(255)';

	const TemplateMustExist = false;

	/**
	 * Fail if a template name is set and the template doesn't exist.
	 *
	 * @param \ValidationResult $result
	 * @return array
	 * @throws \ValidationException
	 */
	public function validate(\ValidationResult $result) {
		$templateName = $this()->TemplateName;

		if (self::TemplateMustExist && $templateName && !\SSViewer::hasTemplate($templateName)) {
			$result->error("Template '$templateName' doesn't exist");
		}
		return parent::validate($result);
	}
}