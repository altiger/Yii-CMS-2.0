<?
return CMap::mergeArray(require(CONFIG . '.php'), array(
    'basePath' => $_SERVER['DOCUMENT_ROOT'].'/protected',
    'language'   => 'en',
//    'components' => array(
//        'commandRunner' => 'application.components.CommandRunner'
//    ),
    'commandMap' => array(
        'migrate'    => array(
            'class' => 'application.commands.ExtendMigrateCommand',
        ),
    ),
));
