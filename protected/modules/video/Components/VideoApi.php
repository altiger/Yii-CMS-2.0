<?php
class VideoApi extends CApplicationComponent
{
    public $apis = array();

    protected $apiStorage;

    public function getApiStorage()
    {
        if ($this->apiStorage == null)
        {
            $this->apiStorage = new SplPriorityQueue();
            foreach ($this->apis as $config)
            {
                $adapter = Yii::createComponent($config);
                $this->apiStorage->insert($adapter, $adapter->priority);
            }

        }
        return $this->apiStorage;
    }

    public function getApi($model)
    {
        foreach ($this->getApiStorage() as $api)
        {
            if ($api->canSave($model))
            {
                return $api;
            }
        }
        return null;
    }

    public function save($model)
    {
        $api = $this->getApi($model);
        $model->api = get_class($api);
        return $api->save($model);
    }

}