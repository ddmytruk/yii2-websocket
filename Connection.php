<?php
/**
 * Created by PhpStorm.
 * User: dmytrodmytruk
 * Date: 05.07.17
 * Time: 14:00
 */

namespace ddmytruk\websocket;

use yii\base\Component;

class Connection extends Component {

    public $servers;

    protected $_instances = [];
    public function getInstance($server) {
        if (!isset($this->_instances[$server])) {
            $this->_instances[$server] = stream_socket_client ($this->servers[$server]['websocket'], $errno, $errstr);//соединямся с мастер-процессом:
        }
        return $this->_instances[$server];
    }
    public function send($message, $server = null) {
        if (!$server) {
            reset($this->servers);
            $server = key($this->servers);
        }
        print_r($this->getInstance($server));
        return fwrite($this->getInstance($server), $message . "\n");
    }

}