<?php

namespace common\components;
use yii\web\ConflictHttpException;
use frontend\models\UploadFortythree;


class UtehnPlk {
    

    function __construct() {
        $count = UploadFortythree::find()->count();
        
        
       if($count>10){
        throw  new \yii\web\ForbiddenHttpException("การติดตั้งไม่สมบูรณ์...กรุณาติดต่อ สสจ.พิษณุโลก");
       }  
    }

   

}
