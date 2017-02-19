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
	 * @param $mode
	 * @return array
	 */
	public function cmsFields($mode) {
		return $this()->isInDB()
			? [$this->gridField()]
			: [$this->saveMasterHint()];
	}

	/**
	 * Return a RelatedModels configured for editing attached MediaModels. If the master record is in the database
	 * then also add GridFieldOrderableRows (otherwise complaint re UnsavedRelationList not being a DataList happens).
	 *
	 * @param string|null $name
	 * @param string|null $configClassName name of grid field configuration class otherwise one is manufactured
	 * @return GridField
	 */
	protected function gridField($name = null, $configClassName = null) {
		$name = $name
			?: static::relationship_name();

		if (!$name) {
			if ($schema = static::related_class_name()) {
				$related = DataObject::get();
			} else {
				$related = \ArrayList::create();
			}
		} else {
			$related = $this()->$name();
		}

		$config = $this->gridFieldConfig($name, $configClassName);

		/** @var HasManyManyGridField $gridField */
		$gridField = \GridField::create(
			$name,
			$name,
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
	 * @param $name
	 * @param $configClassName
	 * @return GridFieldConfig
	 */
	protected function gridFieldConfig($name, $configClassName) {
		$name = $name ?: static::relationship_name();

		$configClassName = $configClassName
			?: static::GridFieldConfigName
				?: get_class($this) . 'GridFieldConfig';

		/** @var GridFieldConfig $config */
		$config = $configClassName::create();

		$schema = static::related_class_name() ?: $name;

		$config->setSearchPlaceholder(
			\Config::inst()->get("$schema.SearchPlaceHolder", "Link existing by Title")
		);
		return $config;
	}

}