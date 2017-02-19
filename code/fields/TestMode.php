<?php
use Modular\Fields\Flag;
use Modular\Types\BoolType;

class TestMode extends Flag implements BoolType {
    const Name = 'TestModeFlag';

    private static $enabled = true;

    public function augmentSQL(SQLQuery &$query) {
        if (!self::enabled()) {
            $query->addWhere(
                self::field_name() . ' != ' . static::YesValue
            );
        }
    }
}