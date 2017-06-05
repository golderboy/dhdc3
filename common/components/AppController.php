<?php

namespace common\components;

use Yii;
use common\components\DhdcKeys;

class AppController extends \yii\web\Controller {

    public function init() {
        parent::init();
    }
    
    protected function overclock($memory='2048M'){
        //ini_set('max_execution_time', 15 * 60);
        set_time_limit(0);
        ini_set('memory_limit', $memory);
    }
    
     protected function exec_sql($sql) {
        $affect_row = \Yii::$app->db->createCommand($sql)->execute();
        return $affect_row;
    }

    

    protected function doLogin(){
        if (\Yii::$app->user->isGuest) {
            $this->redirect(['site/login']);           
        }
    }

    
    protected function getRole(){
        if (!\Yii::$app->user->isGuest) {
             return \Yii::$app->user->identity->role;
         }  else {
             return "?";            
         }
    }

    public function permitRole($role=[]){ 
        
        $r = $this->getRole();   
        if(empty($role)){
             throw  new \yii\web\ForbiddenHttpException("ไม่ได้รับอนุญาต");
             
        }
        if( !in_array($r,$role)){
            throw  new \yii\web\ForbiddenHttpException("ไม่ได้รับอนุญาต");
        }         
        
    }
    
    
    protected function identify_key() {
        $sql = "select district_code from sys_config_main limit 1";
        $raw = \Yii::$app->db->createCommand($sql)->queryOne();
        $distcode = $raw['district_code'];        
        DhdcKeys::permit($distcode);

        
    }
    
    protected function curl(){
        /*$url = "http://203.157.118.125/plkhealthdb/web/index.php?r=dhdc/permit&distcode=$distcode";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        $output = curl_exec($ch);*/
    }

}
