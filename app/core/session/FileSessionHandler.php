<?php

namespace rrdev\core\session;

use rrdev\core;

defined('ROOT') OR die('No direct script access.');

/**
 * Description of FileSessionHandler
 *
 * @author Ivan Kolotilkin
 * 
 */
class FileSessionHandler implements \SessionHandlerInterface {

    private $savePath;
    private $sessionName;

    function open($savePath, $sessionName): bool {
        $this->savePath = $savePath;
        $this->sessionName = $sessionName;
        $ret = true;
        if (!is_dir($this->savePath)) {
            $ret = mkdir($this->savePath, 0755, true);
        }
        return $ret;
    }

    function close(): bool {
        return true;
    }

    function read($id): string|false {
        $sesFile = "$this->savePath/sess_$id";
        if (file_exists($sesFile)) {
            $data = core::StrDecrypt(file_get_contents($sesFile), core::BrouserHash());
            if (is_null($data)) {
                unlink($sesFile);
                return '';
            }
            return $data;
        }
        return '';
    }

    function write($id, $data): bool {
        $dataCrypt = core::StrEncrypt($data, core::BrouserHash());
//        dd($dataCrypt);
        return file_put_contents("$this->savePath/sess_$id", $dataCrypt) === false ? false : true;
    }

    function destroy($id): bool {
        $sesFile = "$this->savePath/sess_$id";
        if (file_exists($sesFile)) {
            unlink($sesFile);
        }
        return true;
    }

    function gc($maxlifetime): int|false {
        foreach (glob("$this->savePath/sess_*") as $file) {
            if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }
        return true;
    }

}
