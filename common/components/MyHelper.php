<?php
namespace common\components;
use yii\base\Component;

/**
 * Description of MyHelper
 *
 * @author utehn
 */
class MyHelper extends Component {

    public static function getUserName() {

        if (!\Yii::$app->user->isGuest) {
            return \Yii::$app->user->identity->username;
        } else {
            return 'guest';
        }
    }
    public static function setAlert($key,$value){
        return \Yii::$app->session->setFlash($key, $value);
    }

}
