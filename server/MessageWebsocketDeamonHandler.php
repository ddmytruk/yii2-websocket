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
//        echo PHP_EOL;
//        print_r($getData);
//        echo PHP_EOL;
//        print_r($this->chanels);
//        echo PHP_EOL;
//        print_r($this->userIds);
//        echo PHP_EOL;
    }

    protected function onClose($connectionId) {

        WSEvent::beforeClose($this, [
            'connectionId' => $connectionId,
        ]);

        unset($this->userIds[$connectionId]);
        foreach ($this->chanels as $key => $value) {
            if(count($this->chanels[$key]))
                unset($this->chanels[$key]);
            else
                unset($this->chanels[$key][$connectionId]);
        }

        WSEvent::afterClose($this, [
            'connectionId' => $connectionId,
        ]);

    }

    private function doSome($data) {
        #\Yii::$app->language = 'en';
        echo PHP_EOL;
        print_r(\Yii::$app->language);
        echo PHP_EOL;
        print_r(Url::to(['/site/index']));
        echo PHP_EOL;
        echo PHP_EOL;

        $objData = json_decode($data);
        $sdata   = $objData->data;
        $channel = $objData->channel;
        $type   = $objData->type;



        $resultData = WSEvent::somethingToDo($channel, $type, $sdata);

        echo PHP_EOL;
        print_r($resultData);
        echo PHP_EOL;

        if(isset($this->chanels[$channel])) {
            foreach ($this->chanels[$channel] as $key => $value) {
                echo PHP_EOL;
                print_r($resultData);
                echo PHP_EOL;
                $this->sendToClient($key, $resultData);
            }
        }
    }

    protected function onMessage($connectionId, $data, $type) {

        if (!strlen($data)) {
            return;
        }
        $this->doSome($data);
    }

    protected function onServiceMessage($connectionId, $data) {
        if (!strlen($data)) {
            return;
        }
        $this->doSome($data);

    }
}