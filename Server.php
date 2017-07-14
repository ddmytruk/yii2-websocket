<?php
/**
 * Created by PhpStorm.
 * User: dmytrodmytruk
 * Date: 05.07.17
 * Time: 14:21
 */

namespace ddmytruk\websocket;


use ddmytruk\websocket\server\MessageWebsocketDeamonHandler;

class Server {

    public $config;

    public function __construct($config) {
        $this->config = $config;
    }

    public function start() {

        $pid = @file_get_contents($this->config['pid']);

        if ($pid) {
            if (posix_getpgid($pid))
                die("already started\r\n");
            else
                unlink($this->config['pid']);
        }

        if (empty($this->config['websocket']) && empty($this->config['localsocket']))
            die("error: config: !websocket && !localsocket \r\n");

        $server = $service = null;

        if (!empty($this->config['websocket'])) {
            //open server socket
            $server = stream_socket_server($this->config['websocket'], $errorNumber, $errorString);
            stream_set_blocking($server, 0);

            if (!$server) {
                die("error: stream_socket_server: $errorString ($errorNumber)\r\n");
            }
        }

        if (!empty($this->config['localsocket'])) {
            //create a socket for the processing of messages from scripts
            $service = stream_socket_server($this->config['localsocket'], $errorNumber, $errorString);
            stream_set_blocking($service, 0);

            if (!$service) {
                die("error: stream_socket_server: $errorString ($errorNumber)\r\n");
            }
        }

        if (!empty($this->config['master'])) {
            //create a socket for the processing of messages from slaves
            $master = stream_socket_client($this->config['master'], $errorNumber, $errorString);
            stream_set_blocking($master, 0);

            if (!$master) {
                die("error: stream_socket_client: $errorString ($errorNumber)\r\n");
            }
        }

        file_put_contents($this->config['pid'], posix_getpid());

        $workerClass = $this->config['class'];
        /** @var MessageWebsocketDeamonHandler $worker */
        $worker = new $workerClass ($server, $service, $master);

        if (!empty($this->config['timer'])) {
            $worker->timer = $this->config['timer'];
        }
        $worker->start();
    }

    public function stop() {
        $pid = @file_get_contents($this->config['pid']);
        if ($pid) {
            posix_kill($pid, (int)SIGKILL);

            for ($i=0;$i=10;$i++) {
                sleep(1);
                echo PHP_EOL;
                print_r($pid);
                echo PHP_EOL;
                #print_r(posix_getpgid($pid));
                echo PHP_EOL;

                if (!posix_getpgid($pid)) {
                    unlink($this->config['pid']);
                    return;
                }
            }

            die("don't stopped\r\n");
        } else {
            die("already stopped\r\n");
        }
    }

    public function restart() {
        $pid = @file_get_contents($this->config['pid']);
        if ($pid) {
            $this->stop();
        }

        $this->start();
    }

}