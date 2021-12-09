<?php

namespace rrdev;

defined('ROOT') OR die('No direct script access.');
/*
 * отправка фалов разбивая его на чанки. и так же возможность докачки файов
 */

class FileStream {

    private $path = "";
    private $stream = "";
    private $buffer = 102400;
    private $start = -1;
    private $end = -1;
    private $size = 0;

    function __construct($filePath) {
        $this->path = $filePath;
    }

    /**
     * Открывает фаил
     */
    private function open() {
        if (!($this->stream = fopen($this->path, 'rb'))) {
            die('Could not open stream for reading');
        }
    }

    /**
     * задает заголовки браузеру
     */
    private function setHeader() {
        ob_get_clean();
        header("Content-Type: " . mime_content_type($this->path));
        header("Cache-Control: max-age=2592000, public");
        header("Expires: " . gmdate('D, d M Y H:i:s', time() + 2592000) . ' GMT');
        header("Last-Modified: " . gmdate('D, d M Y H:i:s', filemtime($this->path)) . ' GMT');
        $this->start = 0;
        $this->size = filesize($this->path);
        $this->end = $this->size - 1;
        header("Accept-Ranges: 0-" . $this->end);

        if (isset($_SERVER['HTTP_RANGE'])) {

            $c_start = $this->start;
            $c_end = $this->end;

            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            if (strpos($range, ',') !== false) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $this->start-$this->end/$this->size");
                exit;
            }
            if ($range == '-') {
                $c_start = $this->size - substr($range, 1);
            } else {
                $range = explode('-', $range);
                $c_start = $range[0];

                $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $c_end;
            }
            $c_end = ($c_end > $this->end) ? $this->end : $c_end;
            if ($c_start > $c_end || $c_start > $this->size - 1 || $c_end >= $this->size) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $this->start-$this->end/$this->size");
                exit;
            }
            $this->start = $c_start;
            $this->end = $c_end;
            $length = $this->end - $this->start + 1;
            fseek($this->stream, $this->start);
            header('HTTP/1.1 206 Partial Content');
            header("Content-Length: " . $length);
            header("Content-Range: bytes $this->start-$this->end/" . $this->size);
        } else {
            header("Content-Length: " . $this->size);
        }
    }

    /**
     * закрывает поток
     */
    private function end() {
        fclose($this->stream);
        exit;
    }

    /**
     * отправляет чанки файла клиенту
     */
    private function stream() {
        $i = $this->start;
//        set_time_limit(0);
        //ignore_user_abort(true); // запрещаем скрипту завершаться при разрыве

        while (!feof($this->stream) && $i <= $this->end) {
            if (connection_status() != CONNECTION_NORMAL) { // соединение с пользователем прекращено
                fclose($this->stream); // принудительно закрываем файл, на всякий пожарный
                exit;
            }
            $bytesToRead = $this->buffer;
            if (($i + $bytesToRead) > $this->end) {
                $bytesToRead = $this->end - $i + 1;
            }
            $data = fread($this->stream, $bytesToRead);
            echo $data;
            flush();
//            ob_flush();
            $i += $bytesToRead;
        }
    }

    /**
     * запускаем работу потока
     */
    function start() {
        $this->open();
        $this->setHeader();
        $this->stream();
        $this->end();
    }

    /**
     * Send file with HTTPRange support (partial download)
     */
    function smartReadFile($location, $filename, $mimeType = 'application/octet-stream') {

        if (!file_exists($location)) {
            header("HTTP/1.0 404 Not Found");
            return false;
        }

        $size = filesize($location);
        $time = date('r', filemtime($location));

        $fm = fopen($location, 'rb');
        if (!$fm) {
            header("HTTP/1.0 505 Internal server error");
            return false;
        }

        $begin = 0;
        $end = $size;

        if (isset($_SERVER['HTTP_RANGE'])) {
            if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches)) {
                $begin = intval($matches[0]);
                if (!empty($matches[1])) {
                    $end = intval($matches[1]);
                }
            }
        }

        if ($begin > 0 || $end < $size) {
            header('HTTP/1.0 206 Partial Content');
        } else {
            header('HTTP/1.0 200 OK');
        }
        header("Content-Type: $mimeType");
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Accept-Ranges: bytes');
        header('Content-Length:' . ($end - $begin));
        header("Content-Range: bytes $begin-$end/$size");
        header("Content-Disposition: inline; filename=$filename");
        header("Content-Transfer-Encoding: binary\n");
        header("Last-Modified: $time");
        header('Connection: close');

        $cur = $begin;
        fseek($fm, $begin, 0);

        while (!feof($fm) && $cur < $end && (connection_status() == CONNECTION_NORMAL)) {
//            usleep(100000); // тупо ограничение закачки
            echo fread($fm, min(1024 * 16, $end - $cur));
            $cur += 1024 * 16;
        }
        fclose($fm); // принудительно закрываем файл, на всякий пожарный
    }

}
