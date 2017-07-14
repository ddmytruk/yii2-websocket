<?php
/**
 * Created by PhpStorm.
 * User: dmytrodmytruk
 * Date: 05.07.17
 * Time: 14:26
 */

namespace ddmytruk\websocket\server;

use app\modules\ide_websocket\components\WSEvent;
use ddmytruk\websocket\Daemon;
use yii\helpers\Url;

class MessageWebsocketDeamonHandler extends Daemon {

    public $userIds = [];

    public $chanels = [];

    protected function onOpen($connectionId, $info) {

        parse_str($info['GET'], $getData);

        foreach ($getData as $key => $value) {
            unset($getData[$key]);
            $getData[str_replace(str_split('/?'), '', $key)] = $value;
        }

        if(isset($getData['c'])) {
            foreach ($getData['c'] as $key => $value) {
                $this->chanels[$key][$connectionId] = true;
            }
        }


        $this->userIds[$connectionId] = $getData['userId'];
        echo PHP_EOL;
        print_r('onOpen');
        echo PHP_EOL;
        print_r($this->chanels);
        echo PHP_EOL;
        echo PHP_EOL;
    }

    protected function onClose($connectionId) {

        WSEvent::beforeClose($this, [
            'connectionId' => $connectionId,
        ]);

//        echo PHP_EOL;
//        print_r('onClose');
//        echo PHP_EOL;
//        print_r($this->chanels);
//        echo PHP_EOL;
//        print_r($connectionId);
//        echo PHP_EOL;
//        echo PHP_EOL;

        unset($this->userIds[$connectionId]);
        foreach ($this->chanels as $key => $value) {
            if($this->chanels[$key] == $connectionId && count($this->chanels[$key]) == 1) {
                unset($this->chanels[$key]);
            }
            else {
                foreach ($this->chanels[$key] as $channelKey => $cnannelValue) {
                    if($channelKey == $connectionId) {
                        unset($this->chanels[$key][$channelKey]);
                    }
                }
            }
        }

        WSEvent::afterClose($this, [
            'connectionId' => $connectionId,
        ]);

    }

    private function doSome($data, $connectionId) {
        #\Yii::$app->language = 'en';
//        echo PHP_EOL;
//        print_r(\Yii::$app->language);
//        echo PHP_EOL;
//        print_r(Url::to(['/site/index']));
//        echo PHP_EOL;
//        echo PHP_EOL;

        $objData = json_decode($data);
        $sdata   = $objData->data;
        $channel = $objData->channel;
        $type   = $objData->type;



        $resultData = WSEvent::somethingToDo($channel, $type, $sdata);

//        echo PHP_EOL;
//        print_r('doSome');
//        echo PHP_EOL;
//        print_r($resultData);
//        echo PHP_EOL;
//        print_r($channel);
//        echo PHP_EOL;
//        echo PHP_EOL;

        if(isset($this->chanels[$channel])) {
            foreach ($this->chanels[$channel] as $key => $value) {
                if($resultData['dataForRecender'] !== null)
                    $this->sendToClient($key, json_encode($resultData['dataForRecender']));
            }
        }

        if($resultData['dataForSender'] !== null)
            $this->sendToClient($connectionId, json_encode($resultData['dataForSender']));
    }

    protected function onMessage($connectionId, $data, $type) {

        if (!strlen($data)) {
            return;
        }

        echo PHP_EOL;
        print_r('onMessage');
        echo PHP_EOL;
        print_r($connectionId);
        #print_r($this->chanels);
        echo PHP_EOL;
        echo PHP_EOL;

        $this->doSome($data, $connectionId);
    }

    protected function onServiceMessage($connectionId, $data) {
        if (!strlen($data)) {
            return;
        }

        echo PHP_EOL;
        print_r($connectionId);
        echo PHP_EOL;

        $this->doSome($data, $connectionId);

    }
}