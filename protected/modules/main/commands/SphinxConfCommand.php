<?php
class SphinxConfCommand extends CConsoleCommand
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
            if (!method_exists($module, 'getSearchInfo'))
            {
                continue;
            }
            /** @var $model ActiveRecord */
            foreach ($module->getSearchInfo() as $index => $models)
            {
                if (!isset($res[$index]))
                {
                    $res[$index] = array();
                }
                $res[$index] = array_merge($res[$index], $models);
            }
        }

        foreach ($res as $index => $models)
        {
            $sqls = $this->prepareCommands($models);
            $union = "\n(\n" . implode("\n) UNION (\n", $sqls) . ')';
            $sql   = 'CREATE OR REPLACE VIEW sphinx_view_' . $index . ' AS ' . $union;
            stop($sql);
            Yii::app()->db->createCommand($sql)->execute();
        }
    }


    private function prepareCommands($models)
    {
        $sqls       = array();
        $all_fields = array();
        $results    = new SplObjectStorage();

        // read columns from query
        /** @var $model ActiveRecord */
        foreach ($models as $model)
        {
            /** @var $a CDbDataReader */
            $fields            = $this->getColumns($model);
            $results[$model] = $fields;
            $all_fields      = array_merge($fields, $all_fields);
        }
        $all_fields = array_unique($all_fields);

        $sqls = array();
        //add null columns for future union using
        foreach ($models as $model)
        {
            $fields = $results[$model];
            //collect new fields with null
            $newFields = array();
            foreach ($all_fields as $f)
            {
                $key = array_search($f, $fields);
                if ($key !== false)
                {
                    $newFields[] = $model->getDbCriteria()->select[$key];
                }
                else
                {
                    $newFields[] =  'null as '.$f;
                }
            }
            $model->getDbCriteria()->select = $newFields;
            //get sql by criteria
            $sqls[] = Yii::app()->db->commandBuilder->createFindCommand($model->tableName(), $model->getDbCriteria())->getText();
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
        $content = file_get_contents(Yii::getPathOfAlias('main.commands.views') . '/base_sphinx.conf');
        foreach (Yii::app()->getModules() as $id => $module)
        {
            $file = Yii::getPathOfAlias($id) . '/sphinx.conf';
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
            'DB_USER'   => Yii::app()->db->username,
            'DB_PASS'   => Yii::app()->db->password,
            'DB_NAME'   => 'cms2',
            //TODO: set data from config
            'DB_HOST'   => 'localhost',
            'BASE_PATH' => 'E:/tools/sphinx',
        ));

        $file = $target . '/sphinx.conf';
        file_put_contents($file, $content);
        return $file;
    }


    public function getColumns($model)
    {
        $command = Yii::app()->db->commandBuilder->createFindCommand($model->tableName(), $model->getDbCriteria());
        Yii::app()->db->createCommand('DROP TABLE IF EXISTS __a')->execute();
        Yii::app()->db->createCommand(
            'CREATE TEMPORARY TABLE IF NOT EXISTS __a (' . $command->getText() . ');')->execute();
        $r = Yii::app()->db->createCommand('SHOW COLUMNS FROM __a;')->queryAll();
        Yii::app()->db->createCommand('DROP TABLE IF EXISTS __a;')->execute();

        $res = array();
        foreach ($r as $field)
        {
            $res[] = $field['Field'];
        }
        return $res;
    }
}