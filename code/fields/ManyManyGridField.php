<?php
namespace Modular\Fields;

use DataObject;
use GridField;
use Modular\GridField\Components\GridFieldOrderableRows;
use Modular\GridField\Configs\GridFieldConfig;
use Modular\Relationships\HasManyMany;

class HasManyManyGridField extends HasManyMany {
	const GridFieldConfigName = 'Modular\GridField\HasManyManyGridFieldConfig';

	private static $cms_tab_name = '';

	private static $sortable = true;

	/**
	 * If model is saved then a gridfield, otherwise a 'save master first' hint.
	 *
	 * @return array
	 */
	public function cmsFields() {
		return $this()->isInDB()
			? [$this->gridField()]
			: [$this->saveMasterHint()];
	}

	/**
	 * Return a RelatedModels configured for editing attached MediaModels. If the master record is in the database
	 * then also add GridFieldOrderableRows (otherwise complaint re UnsavedRelationList not being a DataList happens).
	 *
	 * @param string|null $relationshipName
	 * @param string|null $configClassName name of grid field configuration class otherwise one is manufactured
	 * @return GridField
	 */
	protected function gridField($relationshipName = null, $configClassName = null) {
		$relationshipName = $relationshipName
			?: static::relationship_name();

		if (!$relationshipName) {
			if ($relatedClassName = static::related_class_name()) {
				$related = DataObject::get();
			} else {
				$related = \ArrayList::create();
			}
		} else {
			$related = $this()->$relationshipName();
		}

		$config = $this->gridFieldConfig($relationshipName, $configClassName);

		/** @var HasManyManyGridField $gridField */
		$gridField = \GridField::create(
			$relationshipName,
			$relationshipName,
			$related,
			$config
		);

		if ($this()->isInDB()) {
			// only add if this record is already saved
			$config->addComponent(
				new GridFieldOrderableRows(static::SortFieldName)
			);
		}

		return $gridField;
	}

	/**
	 * Allow override of grid field config
	 *
	 * @param $relationshipName
	 * @param $configClassName
	 * @return GridFieldConfig
	 */
	protected function gridFieldConfig($relationshipName, $configClassName) {
		$relationshipName = $relationshipName ?: static::relationship_name();

		$configClassName = $configClassName
			?: static::GridFieldConfigName
				?: get_class($this) . 'GridFieldConfig';

		/** @var GridFieldConfig $config */
		$config = $configClassName::create();

		$relatedClassName = static::related_class_name() ?: $relationshipName;

		$config->setSearchPlaceholder(
			\Config::inst()->get("$relatedClassName.SearchPlaceHolder", "Link existing by Title")
		);
		return $config;
	}

}