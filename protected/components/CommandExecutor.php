<?php
class CommandExecutor extends CApplicationComponent
{
    /**
     * @var CConsoleCommandRunner
     */
    public $runner;

    public function init()
    {
        $this->runner = new CConsoleCommandRunner();
        AppManager::configureCommandRunner($this->runner);
    }

    public function __call($name, $params)
    {
        $args = array('yiic', $name);
        if (isset($params[0]))
        {
            $params = explode(' ', $params[0]);
        }

        foreach ($params as $item)
        {
            array_push($args, $item);
        }
        try
        {
            ob_start();
            ob_implicit_flush(false);
            array_push($args, '--interactive=0');
            $this->runner->run($args);
            $result = ob_get_clean();
        } catch( Exception $e) {
            ob_clean();
            $result = $e->getMessage();
        }

        return $result;
    }

    public function addCommands($alias)
    {
        $path = Yii::getPathOfAlias($alias);
        if (is_dir($path))
        {
            $this->runner->addCommands($path);
        }
    }

    public function addCommandsFromModules($modules)
    {
        foreach ($modules as $module)
        {
            Yii::app()->executor->addCommands($module.'.commands');
        }
    }
}