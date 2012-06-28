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
        $model = new VideoFile();
        $model->attributes = array(
            'tmp_file' => 'c:\1.mp4',
            'title' => 'tmp_title',
            'descr' => 'tmp_descr',
        );

        dump($model->save());

        /*
                include Yii::getPathOfAlias('video.vendors.vimeo-php-sdk').'/vimeo.php';
                $id = Yii::app()->params->video['vimeo']['client_id'];
                $secret = Yii::app()->params->video['vimeo']['client_secret'];

                $vimeo = new phpVimeo($id, $secret);
                $videos = $vimeo->call('vimeo.videos.getUploaded', array('user_id' => 'brad'));
                $videos = $vimeo->call('vimeo.videos.upload.getQuota');
        dump($videos,0,9);



        */


        //App Name: cms-video-uploader
        //App Namespace: yii-cms
//        include Yii::getPathOfAlias('video.vendors.facebook-php-sdk').'/facebook.php';
//        $id = Yii::app()->params->video['facebook']['app_id'];
//        $facebook = new Facebook( array(
//            'appId'  => $id,
//            'secret' => Yii::app()->params->video['facebook']['app_secret'],
//            'fileUpload' => true
//        ));
//        dump($facebook->getLoginUrl(array(
//            'scope' => 'user_photos,user_birthday,email,user_website,user_hometown,user_checkins,user_events,create_event,read_requests,manage_pages,user_interests,user_notes,friends_photos,user_about_me,user_likes,user_videos,friends_videos,publish_actions,user_religion_politics,user_groups,user_games_activity,friends_groups,friends_games_activity,user_location,user_relationships,user_subscriptions,publish_stream,status_update,photo_upload,video_upload,publish_checkins,read_stream,read_insights,user_online_presence,offline_access,export_stream,sms,share_item,ads_management,read_friendlists,create_note,manage_notifications,manage_friendlists,xmpp_login'
//        )));
//        dump($facebook->api('/' . $id . '/photos?access_token='.$facebook->getAccessToken(), 'post', array('source' => "@c:/1.jpg", 'name' => 'go')));
//        dump($facebook->api('//videos'));
    }
}