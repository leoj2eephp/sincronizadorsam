<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;

/**
 * BaseRbacController is the Base Controller to check permmissions in Yii
 */
class BaseRbacController extends Controller {

    /**
     * @inheritdoc
     */
    public function beforeAction($action) {
        // your custom code here, if you want the code to run before action filters,
        // which are triggered on the [[EVENT_BEFORE_ACTION]] event, e.g. PageCache or AccessControl

        if (!parent::beforeAction($action)) {
            return false;
        }
        $controller_name = $action->controller->id;
        $action_name = $action->id;

        if (Yii::$app->user->can($controller_name . "/" . $action_name) || Yii::$app->user->can("admin")) {
            return true;
        } else {
            throw new \yii\web\ForbiddenHttpException('');
        }
    }
}
