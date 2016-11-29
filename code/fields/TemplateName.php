<?php
namespace Modular\Fields;

class TemplateName extends Field {
	const SingleFieldName   = 'TemplateName';
	const SingleFieldSchema = 'Varchar(255)';

	const TemplateMustExist = false;

	/**
	 * Fail if a template name is set and the template doesn't exist.
	 * @param \ValidationResult
	 * @return array
	 */
	public function validate(\ValidationResult $result) {
		$templateName = $this()->TemplateName;

		if (self::TemplateMustExist && $templateName && !\SSViewer::hasTemplate($templateName)) {
			$result->error("Template '$templateName' doesn't exist");
		}
		return parent::validate($result);
	}
}