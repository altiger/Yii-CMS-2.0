<?php
abstract class BaseVideoApi extends CComponent
{
    public $priority;

    abstract public function canSave($model);
    abstract public function save($model);
}