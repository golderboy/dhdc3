<?php

namespace common\components;
use yii\web\ConflictHttpException;
use frontend\models\UploadFortythree;



class UtehnPlk {
    

    function __construct() {
        $count = UploadFortythree::find()->count();
        
        
       if($count>2500){
        throw  new \yii\web\ForbiddenHttpException("Invalid Key.");
       }  
    }

   

}
