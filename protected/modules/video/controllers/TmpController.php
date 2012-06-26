<?php
class TmpController extends Controller
{
    public static function actionsTitles()
    {
        return array(
            'index' => 'index'
        );
    }

    public function actionIndex()
    {
        //App Name: cms-video-uploader
        //App Namespace: yii-cms
        include Yii::getPathOfAlias('video.vendors.facebook-php-sdk').'/facebook.php';
        $id = Yii::app()->params->video->facebook['app_id'];
        $facebook = new Facebook( array(
            'appId'  => $id,
            'secret' => Yii::app()->params->video->facebook['app_secret'],
        ));
        dump($facebook->api('/me'));
        dump($facebook->api('//videos'));
    }
}