<?php

namespace rrdev\core\session;

use rrdev\core;

defined('ROOT') OR die('No direct script access.');
/*
 * клас для работы с сессиями
 * он настолько простой что нечего тут даже и пояснять.
 * 
 */

class SessionManager {

    /**
     * Determine if session has started.
     *
     * @var boolean
     */
    private static $sessionStarted = false;

    public static function GarbageCollector($maxlifetime = 7200) {
        $LogMsg = 'Session garbage collector start' . PHP_EOL;
        foreach (glob(SESSION_DIR . DS . 'sess_*') as $file) {
            $LogMsg .= "Dele Ses file $file" . PHP_EOL;
            if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                $LogMsg .= "Dele Ses file $file " . time() . PHP_EOL;
                unlink($file);
            }
        }
        $LogMsg .= 'Session garbage collector stop' . PHP_EOL;
        return $LogMsg;
    }

    /**
     * if session has not started, start sessions
     */
    private static function SecSessionStart() {
        ini_set("session.gc_probability", 30); /* Можно настроить на 100%, если у вас там нет никакого медленного кода */
        ini_set("session.gc_divisor", 100);
        ini_set("session.gc_maxlifetime", 7200); /* Время жизни сессии в секундах (то самое, которое передается в функцию gc) */

        if (SESSION_DIR !== false) {
            if (!is_dir(SESSION_DIR)) {
                mkdir(SESSION_DIR, 0755, true);
            }
            session_save_path(SESSION_DIR);
        }

        // Forces sessions to only use cookies.
        if (ini_set('session.use_only_cookies', 1) === FALSE) {
            die(_('Failed to start a secure session'));
        }
        $cookieParams = session_get_cookie_params();
        session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], false);
       
        $handler = null;
        switch (strtolower(SESSION_DRIVER)) {
            case "database":
                $handler = new DatabBaseSessionHandler();
                break;
            case "file":
                $handler = new FileSessionHandler();
                break;
        }
//        dd($handler);
        if($handler){
            session_set_save_handler($handler, true);
        }
        // Start the PHP session 
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
//        session_gc();
//        session_regenerate_id();    // regenerated the session, delete the old one. 
    }

    public static function init() {
        if (!self::$sessionStarted) {
            self::SecSessionStart();
            self::$sessionStarted = true;
        }
        return self::$sessionStarted;
    }

    /**
     * Add value to a session.
     *
     * @param string $key name the data to save
     * @param string|bool $value the data to save
     */
    public static function set($key, $value = false) {
        if (is_array($key) && $value === false) {
            foreach ($key as $name => $value) {
                $_SESSION[$name] = $value;
            }
        } else {
            $_SESSION[$key] = $value;
        }
    }

    /**
     * Extract item from session then delete from the session, finally return the item.
     *
     * @param  string $key item to extract
     * @return mixed|null
     */
    public static function pull($key) {
        if (isset($_SESSION[$key])) {
            $value = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $value;
        }
        return null;
    }

    /**
     * Get item from session
     *
     * @param  string $key item to look for in session
     * @param  boolean $secondkey if used then use as a second key
     * @return mixed|null
     */
    public static function get($key, $secondkey = false) {
        if ($secondkey == true) {
            if (isset($_SESSION[$key][$secondkey])) {
                return $_SESSION[$key][$secondkey];
            }
        } else {
            if (isset($_SESSION[$key])) {
                return $_SESSION[$key];
            }
        }
        return null;
    }

    /**
     * id
     *
     * @return string with the session id.
     */
    public static function id() {
        return session_id();
    }

    /**
     * Regenerate session_id.
     *
     * @return string session_id
     */
    public static function regenerate() {
        session_regenerate_id(true);
        return session_id();
    }

    /**
     * Empties and destroys the session.
     *
     * @param  string $key - session name to destroy
     * @param  boolean $prefix - if set to true clear all sessions for current SESSION_PREFIX
     */
    public static function destroy($key = '', $prefix = false) {
        /** only run if session has started */
        if (self::$sessionStarted == true) {
            // get session parameters 
            $params = session_get_cookie_params();

            // Delete the actual cookie. 
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
            /** if key is empty and $prefix is false */
            if ($key == '' && $prefix == false) {
                session_unset();
                session_destroy();
            } elseif ($prefix == true) {
                /** clear all session for set SESSION_PREFIX */
                foreach ($_SESSION as $key => $value) {
//                    if (strpos($key, self::$sessionName) === 0) {
                    unset($_SESSION[$key]);
//                    }
                }
            } else {
                /** clear specified session key */
                unset($_SESSION[$key]);
            }
        }
    }

    public static function destroyAll() {
        if (self::$sessionStarted) {
            session_unset();
            session_destroy();
            session_write_close();
            session_abort();
        }
    }

}
