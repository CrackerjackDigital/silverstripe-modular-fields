<?php
use Modular\Fields\Flag;

class TestMode extends Flag  {
    const FieldName = 'TestModeFlag';

    private static $enabled = true;

    public function augmentSQL(SQLQuery &$query) {
        if (!self::enabled()) {
            $query->addWhere(
                self::FieldName . ' != ' . self::YesValue
            );
        }
    }
}