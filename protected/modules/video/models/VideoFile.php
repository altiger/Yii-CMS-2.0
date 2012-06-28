<?php
class VideoFile extends FileManager
{
    public function save($runValidation=true,$attributes=null)
    {
        if (Yii::app()->videoApi->save($this))
        {
            return parent::save($runValidation,$attributes);
        }
        return false;
    }
}