<?php

/*
 * การถอดรหัสไฟล์ แสดงถึงวัตถุประสงค์ในการละเมิดลิขสิทธิ์ของผลิตภัณฑ์
 */

namespace common\components;

class DhdcKeys {

    public static function permit($distcode = NULL) {
        $amp = [
            6501 => '20170930',
            6502 => '20170930',
            6503 => '20170930',
            6504 => '20170930',
            6505 => '20170930',
            6506 => '20170930',
            6507 => '20170930',
            6508 => '20170930',
            6509 => '20170930',
            6012 => '20170930'
        ];

        $now = date('Ymd');

        if (empty($amp[$distcode])) {
            throw new \yii\web\ConflictHttpException("$distcode Invalid Key.Plasce contact DHDC Team.");
        } else {
            if ($now > $amp[$distcode]) {
                throw new \yii\web\ConflictHttpException("$distcode  Key Expire.Plasce contact DHDC Team.");
            }
        }
    }

}
