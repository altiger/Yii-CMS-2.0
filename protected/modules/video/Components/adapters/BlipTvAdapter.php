<?php
Yii::import('video.components.adapters.BaseVideoUploadAdapter');
class BlipTvAdapter extends BaseVideoUploadAdapter
{
    public $login;
    public $pass;
    protected $api;

    public function canSave($model)
    {
        return true;
    }

    public function save($model)
    {
        set_time_limit(-1);
        $response = $this->api()->upload($model->tmp_path, $model->title, $model->descr);

        if ($response instanceof SimpleXMLElement)
        {
            $model->attributes = array(
                'internal_id' => $response->payload->asset->item_id
            );
            return true;
        }
        return false;
    }

    public function api()
    {
        if ($this->api === null)
        {
            include Yii::getPathOfAlias('video.vendors.blip-php-sdk').'/blipPHP.php';
            $this->api = new blipPHP($this->login, $this->pass);
        }
        return $this->api;
    }
}