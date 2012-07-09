<?php
class SphinxConf extends CConsoleCommand
{
    public $basePath = 'application.runtime.sphinx';
    public $targetPath = 'application.runtime.sphinx';

    public $indexer = 'E:/tools/sphinx/indexer.exe';
    public $searchd = 'E:/tools/sphinx/searchd.exe';

    public function run()
    {
        $this->buildDbViews();
        $config = $this->getConfig();
        $this->runSphinx($config);
    }

    private function buildDbViews()
    {

    }

    private function runSphinx($config_file)
    {
        $searchd = "{$this->searchd} --config $config_file";

        //TODO: why at WIN on first run server - cmd stay wait???
        $is_win = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        //run server
        echo $is_win ? exec("start /b $searchd") : exec("$searchd &");
        //reindex
        echo system("{$this->indexer} --config $config_file --all --rotate");
    }

    private function getConfig()
    {
        $content = file_get_contents(Yii::getPathOfAlias('main.commands.views').'/base_sphinx.conf');
        foreach (Yii::app()->getModules() as $id => $module)
        {
            $file = Yii::getPathOfAlias($id).'/sphinx.conf';
            if (is_file($file))
            {
                $content .= file_get_contents($file);
            }
        }
        $base = Yii::getPathOfAlias($this->basePath);
        is_dir($base) || mkdir($base, 0777);
        $target = Yii::getPathOfAlias($this->targetPath);
        is_dir($target) || mkdir($target, 0777);

        $content = Yii::app()->text->parseTemplate($content, array(
            'DB_USER' => Yii::app()->db->username,
            'DB_PASS' => Yii::app()->db->password,
            'DB_NAME' => 'cms2', //TODO: set data from config
            'DB_HOST' => 'localhost',
            'BASE_PATH' => 'E:/tools/sphinx',
        ));

        $file = $target.'/sphinx.conf';
        file_put_contents($file, $content);
        return $file;
    }
}