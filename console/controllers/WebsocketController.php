<?php
/**
 * Created by PhpStorm.
 * User: dmytrodmytruk
 * Date: 05.07.17
 * Time: 14:15
 */

namespace ddmytruk\websocket\console\controllers;

use Yii;
use ddmytruk\websocket\Server;
use yii\console\Controller;
use yii\helpers\Url;

class WebsocketController extends Controller {

    public $component = 'websocket';

    public function actionStart($server) {
        $websocketServer = new Server(Yii::$app->get($this->component)->servers[$server]);
        call_user_func(array($websocketServer, 'start'));
    }

    public function actionStop($server) {
        $WebsocketServer = new Server(Yii::$app->get($this->component)->servers[$server]);
        call_user_func(array($WebsocketServer, 'stop'));
    }

    public function actionRestart($server) {
        $WebsocketServer = new Server(Yii::$app->get($this->component)->servers[$server]);
        call_user_func(array($WebsocketServer, 'restart'));
    }

}