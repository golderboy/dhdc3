<?php

/*
 * เปลี่ยน  '/n' เป็น /r/n  ใน LOAD DATA
 * เปลี่ยน  $ext == 'txt'  เป็น  strtolower($ext) == 'txt'
 * message  'admin do import all'  เป็น  'import all'
 */

namespace frontend\controllers;

use yii;
use yii\helpers\Html;
use frontend\models\UploadFortythree;
use frontend\models\SysCountImport;

class AjaxController extends \yii\web\Controller {

    public function actionIndex() {
        return $this->render('index');
    }

    //import on window
    public function actionImport($fortythree, $upload_date, $upload_time, $id) {

        ini_set('max_execution_time', 0);        

        $model = UploadFortythree::findOne($id);
        $model->note2 = 'กำลังนำเข้า';
        $model->update();



        $filefortythree = "fortythree/$fortythree";
        $zip = new \ZipArchive();
        if ($zip->open($filefortythree) === TRUE) {
            $zip->extractTo("fortythree");
            $zip->close();
        }
        //rename($path . $oldname, $path . $fortythree);

        $folder_with_ext = explode('.', $fortythree);
        $folder_without_ext = $folder_with_ext[0];

        $full_dir = "fortythree/$folder_without_ext";
        $dir = opendir($full_dir);

        $cfmodel = new SysCountImport();
        $cfmodel->import_date = date('YmdHis');
        $cfmodel->filename = $fortythree;
        $cfmodel->upload_date = $upload_date;
        $cfmodel->upload_time = $upload_time;

        while (($file = readdir($dir)) !== false) {
            if ($file !== "." && $file !== "..") {
                $model->note3 = $file;
                $model->update();

                $p = pathinfo($file);
                $ftxt = $p['filename'];
                $ftxt = strtolower($ftxt);
                $ext = $p['extension'];
                if (strtolower($ext) == 'txt' && $ftxt !== 'office') {

                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        //raw
                        $sql = "LOAD DATA LOCAL INFILE 'fortythree/$folder_without_ext/$file'";
                        $sql.= " REPLACE INTO TABLE $ftxt";
                        $sql.= " FIELDS TERMINATED BY '|'  LINES TERMINATED BY '\r\n' IGNORE 1 LINES";
                        $raw = \Yii::$app->db->createCommand($sql)->execute();

                        //tmp                        
                        $sql = "LOAD DATA LOCAL INFILE 'fortythree/$folder_without_ext/$file'";
                        $sql.= " REPLACE INTO TABLE tmp_$ftxt";
                        $sql.= " FIELDS TERMINATED BY '|'  LINES TERMINATED BY '\r\n' IGNORE 1 LINES";
                        $sql.= " SET NOTE1='$fortythree',NOTE2=NOW()";
                        $tmp = \Yii::$app->db->createCommand($sql)->execute();

                        // count
                        $sql = " REPLACE  INTO sys_count_import_file  (
                                 SELECT IF(NOTE1 is NULL,'$fortythree','$fortythree'),'$ftxt',COUNT(*),NOW(),'','','' FROM tmp_$ftxt
                                 WHERE NOTE1 = '$fortythree'
                            );  ";
                        \Yii::$app->db->createCommand($sql)->execute();

                        $sql = "DELETE FROM tmp_$ftxt WHERE NOTE1 = '$fortythree' ";
                        \Yii::$app->db->createCommand($sql)->execute();

                        $transaction->commit();
                    } catch (Exception $e) {
                        $transaction->rollBack();
                    }
                }
            }
        }

        closedir($dir);

        $dir = opendir($full_dir);
        while (($file = readdir($dir)) !== false) {
            if ($file !== "." && $file !== "..") {
                if ($file !== "." && $file !== "..") {
                    unlink("fortythree/$folder_without_ext/$file");
                }
            }
        }

        closedir($dir);

        rmdir("fortythree/$folder_without_ext");
        unlink("fortythree/$fortythree");

        //$model = UploadFortythree::findOne($id);
        $model->note3 = '';
        $model->note2 = 'OK';
        $model->update();
        return $fortythree;
    }

    //import on linux
    public function actionImport2($fortythree, $upload_date, $upload_time, $id) {

        ini_set('max_execution_time', 0);

        $model = UploadFortythree::findOne($id);
        $model->note2 = 'กำลังนำเข้า';
        $model->update();

        //$ubuntu_path = "/var/lib/mysql/fortythree/";

        $rootpath = \Yii::getAlias('@webroot') . "/fortythree/";
        $filefortythree = $rootpath . $fortythree;
        $zip = new \ZipArchive();
        if ($zip->open($filefortythree) === TRUE) {
            $zip->extractTo($rootpath);
            $zip->close();
        }
        //rename($path . $oldname, $path . $fortythree);


        $folder_with_ext = explode('.', $fortythree);
        $folder_without_ext = $folder_with_ext[0];

        $full_dir = "$rootpath/$folder_without_ext";
        $dir = opendir($full_dir);

        $cfmodel = new SysCountImport();
        $cfmodel->import_date = date('YmdHis');
        $cfmodel->filename = $fortythree;
        $cfmodel->upload_date = $upload_date;
        $cfmodel->upload_time = $upload_time;


        while (($file = readdir($dir)) !== false) {
            if ($file !== "." && $file !== "..") {
                $model->note3 = $file;
                $model->update();

                $p = pathinfo($file);
                $ftxt = $p['filename'];
                $ftxt = strtolower($ftxt);
                $ext = $p['extension'];
                if (strtolower($ext) == 'txt' && $ftxt !== 'office') {

                    $transaction = \Yii::$app->db->beginTransaction();
                    try {

                        $sql = "LOAD DATA LOCAL INFILE '$rootpath$folder_without_ext/$file'";
                        $sql.= " REPLACE INTO TABLE $ftxt";
                        $sql.= " FIELDS TERMINATED BY '|'  LINES TERMINATED BY '\r\n' IGNORE 1 LINES";
                        $count = \Yii::$app->db->createCommand($sql)->execute();

                        //tmp                        
                        $sql = "LOAD DATA LOCAL INFILE 'fortythree/$folder_without_ext/$file'";
                        $sql.= " REPLACE INTO TABLE tmp_$ftxt";
                        $sql.= " FIELDS TERMINATED BY '|'  LINES TERMINATED BY '\r\n' IGNORE 1 LINES";
                        $sql.= " SET NOTE1='$fortythree',NOTE2=NOW()";
                        $tmp = \Yii::$app->db->createCommand($sql)->execute();

                        // count
                        $sql = " REPLACE  INTO sys_count_import_file  (
                                 SELECT IF(NOTE1 is NULL,'$fortythree','$fortythree'),'$ftxt',COUNT(*),NOW(),'','','' FROM tmp_$ftxt
                                 WHERE NOTE1 = '$fortythree'
                            );  ";
                        \Yii::$app->db->createCommand($sql)->execute();

                        $sql = "DELETE FROM tmp_$ftxt WHERE NOTE1 = '$fortythree' ";
                        \Yii::$app->db->createCommand($sql)->execute();


                        $transaction->commit();
                    } catch (Exception $e) {
                        $transaction->rollBack();
                    }
                }
            }
        }

        closedir($dir);

        $dir = opendir($full_dir);
        while (($file = readdir($dir)) !== false) {
            if ($file !== "." && $file !== "..") {
                if ($file !== "." && $file !== "..") {
                    unlink("$rootpath$folder_without_ext/$file");
                }
            }
        }

        closedir($dir);

        rmdir("$rootpath$folder_without_ext");
        unlink("$rootpath$fortythree");

        //$model = UploadFortythree::findOne($id);
        $model->note3 = '';
        $model->note2 = 'OK';
        $model->update();
        return $fortythree;
    }

    //import all on window
    public function actionImport3($fortythree, $upload_date, $upload_time) {

        ini_set('max_execution_time', 0);

        $filefortythree = "fortythree/$fortythree";

        $file_size = number_format(filesize($filefortythree) / (1024 * 1024), 3);
        $file_size = strval($file_size);
        $zip = new \ZipArchive();
        if ($zip->open($filefortythree) === TRUE) {
            $zip->extractTo("fortythree");
            $zip->close();
        }
        unlink("fortythree/$fortythree");
        //rename($path . $oldname, $path . $fortythree);

        $folder_with_ext = explode('.', $fortythree);
        $folder_without_ext = $folder_with_ext[0];

        $full_dir = "fortythree/$folder_without_ext";
        $dir = opendir($full_dir);

        $cfmodel = new SysCountImport();
        $cfmodel->import_date = date('YmdHis');
        $cfmodel->filename = $fortythree;
        $cfmodel->upload_date = $upload_date;
        $cfmodel->upload_time = $upload_time;

        while (($file = readdir($dir)) !== false) {
            if ($file !== "." && $file !== "..") {


                $p = pathinfo($file);
                $ftxt = $p['filename'];
                $ftxt = strtolower($ftxt);
                $ext = $p['extension'];
                if (strtolower($ext) == 'txt' && $ftxt !== 'office') {

                    $transaction = \Yii::$app->db->beginTransaction();
                    try {

                        $sql = "LOAD DATA LOCAL INFILE 'fortythree/$folder_without_ext/$file'";
                        $sql.= " REPLACE INTO TABLE $ftxt";
                        $sql.= " FIELDS TERMINATED BY '|'  LINES TERMINATED BY '\r\n' IGNORE 1 LINES";
                        \Yii::$app->db->createCommand($sql)->execute();
                        $transaction->commit();
                    } catch (Exception $e) {
                        $transaction->rollBack();
                    }
                }
            }
        }
        //$cfmodel->save();

        $upload = new UploadFortythree;
        $upload->file_name = $fortythree;
        $upload->file_size = $file_size;
        $fff = explode('_', $fortythree);
        $upload->hospcode = $fff[1];
        $upload->upload_date = date('Ymd');
        $upload->upload_time = date('His');
        $upload->note2 = 'OK';
        $upload->note3 = 'import all';
        $upload->save();

        $up = UploadFortythree::findOne(['file_name' => $fortythree]);
        if ($up) {
            $up->note2 = 'OK';
            $up->note3 = 'import all';
            $up->update();
        }


        closedir($dir);


        $dir = opendir($full_dir);
        while (($file = readdir($dir)) !== false) {
            if ($file !== "." && $file !== "..") {
                if ($file !== "." && $file !== "..") {
                    unlink("fortythree/$folder_without_ext/$file");
                }
            }
        }

        closedir($dir);

        rmdir("fortythree/$folder_without_ext");
        //unlink("fortythree/$fortythree");

        return $fortythree;
    }

    //import all on linux
    public function actionImport4($fortythree, $upload_date, $upload_time) {

        ini_set('max_execution_time', 0);

        //$ubuntu_path = "/var/lib/mysql/fortythree/";

        $rootpath = \Yii::getAlias('@webroot') . "/fortythree/";
        $filefortythree = $rootpath . $fortythree;

        $file_size = number_format(filesize($filefortythree) / (1024 * 1024), 3);
        $file_size = strval($file_size);

        $zip = new \ZipArchive();
        if ($zip->open($filefortythree) === TRUE) {
            $zip->extractTo($rootpath);
            $zip->close();
        }
        //rename($path . $oldname, $path . $fortythree);
        unlink("$rootpath$fortythree");

        $folder_with_ext = explode('.', $fortythree);
        $folder_without_ext = $folder_with_ext[0];

        $full_dir = "$rootpath/$folder_without_ext";
        $dir = opendir($full_dir);

        $cfmodel = new SysCountImport();
        $cfmodel->import_date = date('YmdHis');

        $cfmodel->filename = $fortythree;
        $cfmodel->upload_date = $upload_date;
        $cfmodel->upload_time = $upload_time;


        while (($file = readdir($dir)) !== false) {
            if ($file !== "." && $file !== "..") {


                $p = pathinfo($file);
                $ftxt = $p['filename'];
                $ftxt = strtolower($ftxt);
                $ext = $p['extension'];
                if (strtolower($ext) == 'txt' && $ftxt !== 'office') {

                    $transaction = \Yii::$app->db->beginTransaction();
                    try {

                        $sql = "LOAD DATA LOCAL INFILE '$rootpath$folder_without_ext/$file'";
                        $sql.= " REPLACE INTO TABLE $ftxt";
                        $sql.= " FIELDS TERMINATED BY '|'  LINES TERMINATED BY '\r\n' IGNORE 1 LINES";
                        \Yii::$app->db->createCommand($sql)->execute();
                        $transaction->commit();
                    } catch (Exception $e) {
                        $transaction->rollBack();
                    }
                }
            }
        }



        $upload = new UploadFortythree;
        $upload->file_name = $fortythree;
        $upload->file_size = $file_size;
        $fff = explode('_', $fortythree);
        $upload->hospcode = $fff[1];
        $upload->upload_date = date('Ymd');
        $upload->upload_time = date('His');
        $upload->note2 = 'OK';
        $upload->note3 = 'import all';
        $upload->save();


        $up = UploadFortythree::findOne(['file_name' => $fortythree]);
        if ($up) {
            $up->note2 = 'OK';
            $up->note3 = 'import all';
            $up->update();
        }


        closedir($dir);

        $dir = opendir($full_dir);
        while (($file = readdir($dir)) !== false) {
            if ($file !== "." && $file !== "..") {
                if ($file !== "." && $file !== "..") {
                    unlink("$rootpath$folder_without_ext/$file");
                }
            }
        }

        closedir($dir);

        rmdir("$rootpath$folder_without_ext");



        return $fortythree;
    }

    public function actionTruncate() {

        if (!\Yii::$app->user->isGuest) {
            $user = Html::encode(Yii::$app->user->identity->username);

            if ($user == 'admin') {

                ini_set('max_execution_time', 0);
                $model = \frontend\models\SysFiles::find()->asArray()->all();
                foreach ($model as $m) {
                    $table = $m['file_name'];
                    $sql = "truncate $table";
                    \Yii::$app->db->createCommand($sql)->execute();
                    echo $sql . "<br>";
                }

                \Yii::$app->db->createCommand("truncate sys_upload_fortythree;")->execute();
                \Yii::$app->db->createCommand("truncate sys_count_import;")->execute();
                \Yii::$app->db->createCommand("truncate sys_count_all;")->execute();
                \Yii::$app->db->createCommand("truncate sys_count_service;")->execute();
                \Yii::$app->db->createCommand("truncate sys_person_type;")->execute();

                // \Yii::$app->db->createCommand("truncate sys_ncd_nocholesteral_colorchart;")->execute();
                // \Yii::$app->db->createCommand("truncate sys_ncd_cholesteral_colorchart;")->execute();
            }
        }
    }

}
