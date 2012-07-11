<?php
class SphinxConf extends CConsoleCommand
{
    public $basePath = 'application.runtime.sphinx';
    public $targetPath = 'application.runtime.sphinx';

    public $indexer = 'E:/tools/sphinx/indexer';
    public $searchd = 'E:/tools/sphinx/searchd';

    public function run()
    {
        $this->buildDbViews();
        $config = $this->getConfig();
        $this->runSphinx($config);
    }

    private function buildDbViews()
    {
        $res = array();
        foreach (Yii::app()->getModules() as $id => $module)
        {
            $module = Yii::app()->getModule($id);
            if (!method_exists($module, 'getSqlForSearchData'))
            {
                continue;
            }
            $data = $module->getSqlForSearchData();
            foreach ($data as $index => $sql)
            {
                $res[$index][] = $sql;
            }
        }

        foreach ($res as $index => $commands)
        {
            $sqls = $this->prepareCommands($commands);
            $union = "\n(\n".implode("\n) UNION (\n",$sqls) . ')';
            $sql = 'CREATE OR REPLACE VIEW sphinx_view_'.$index.' AS '.$union;
            Yii::app()->db->createCommand($sql)->execute();
        }
    }

    private function prepareCommands($commands)
    {
        $sqls = array();
        $all_fields = array();
        $results = new SplObjectStorage();

        // read first row of all queries for collecting all_fields
        /** @var $command CDbCommand */
        foreach ($commands as $command)
        {
            /** @var $a CDbDataReader */
            $fields = array_keys($command->queryRow());
            $results[$command] = $fields;
            $all_fields = array_merge($all_fields, $fields);
        }
        $all_fields = array_unique($fields);

        //add null columns for all commands
        foreach ($commands as $command)
        {
            $fields = $results[$command];
            for ($i = 0; $i < count($fields); $i++)
            {
                if ($fields[$i] != $all_fields[$i])
                {
                    $fields = array_splice($fields, $i, 0, $all_fields[$i]);
                }
            }

            $sqls[] = $command->getText();
        }
        return $sqls;
    }

    private function runSphinx($config_file)
    {
        //stop daemon
//        system("{$this->searchd} --stop --config $config_file");

        //reindex
        system("{$this->indexer} --config $config_file --all --rotate");
die;
        //run daemon
        $searchd = "{$this->searchd} --config $config_file";

        //TODO: why at WIN on first run server - cmd stay wait???
        $is_win = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        //run server
        $is_win ? system("start /b $searchd") : exec("$searchd &");
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