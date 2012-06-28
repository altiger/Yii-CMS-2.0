<?php
abstract class BaseVideoUploadAdapter extends CComponent
{
    public $priority;

    abstract public function canSave($model);
    abstract public function save($model);
}