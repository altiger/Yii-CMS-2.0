<?php
class VideoApi extends CApplicationComponent
{
    public $adapters = array();

    protected $adapterStorage;

    public function getAdapterStorage()
    {
        if ($this->adapterStorage == null)
        {
            $this->adapterStorage = new SplPriorityQueue();
            foreach ($this->adapters as $config)
            {
                $adapter = Yii::createComponent($config);
                $this->adapterStorage->insert($adapter, $adapter->priority);
            }

        }
        return $this->adapterStorage;
    }

    public function getAdapter($model)
    {
        foreach ($this->getAdapterStorage() as $adapter)
        {
            if ($adapter->canSave($model))
            {
                return $adapter;
            }
        }
        return null;
    }

    public function save($model)
    {
        $adapter = $this->getAdapter($model);
        $model->adapter = get_class($adapter);
        return $adapter->save($model);
    }

}