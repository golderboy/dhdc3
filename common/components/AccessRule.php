<?php

namespace common\components;

use common\models\User;

class AccessRule extends \yii\filters\AccessRule {

    /**
     * @inheritdoc
     */
    protected function matchRole($user) {

        if (empty($this->roles)) {
            return true;
        }
        foreach ($this->roles as $role) {

            if ($role == '?' && $user->getIsGuest()) {
                return true;
            } elseif ($role == 0 && !$user->getIsGuest()) {
                return true;
            } elseif (!$user->getIsGuest() && $role == $user->identity->role) {
                return true;
            }
        }//end for

        return false;
    }

}
