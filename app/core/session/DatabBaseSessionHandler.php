<?php

namespace rrdev\core\session;

use rrdev\core\Model;
/**
//Database
CREATE TABLE `Session` (
  `Session_Id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Session_Expires` datetime NOT NULL,
  `Session_Data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`Session_Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SELECT * FROM mydatabase.Session;
 */

/**
 * Description of DatabBaseSessionHandler
 *
 * @author Ivan Kolotilkin
 * 
 */
class DatabBaseSessionHandler implements \SessionHandlerInterface {

    private $model;

    public function open($savePath, $sessionName): bool {
        $this->model = new Model();
        $sql = "CHECK TABLE session";
        $row = $this->model->getRow($sql);
        if($row['Msg_type']==='Error'){
            $sql = "CREATE TABLE `session` (
  `session_Id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `session_Expires` datetime NOT NULL,
  `session_Data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`session_Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
            $row = $this->model->exec($sql);
//            dd($row);
        }
        
        $link = $this->model->getWriter();
        if ($link) {
            return true;
        } else {
            return false;
        }
    }

    public function close(): bool {
//        mysqli_close($this->link);
        return true;
    }

    public function read($id): string|false {
//        $se = date('Y-m-d H:i:s');
//        $sql = "SELECT Session_Data FROM Session WHERE Session_Id = ':id' AND Session_Expires > ':se'";
        $sql = "SELECT session_Data FROM Session WHERE Session_Id = '".$id."' AND Session_Expires > '".date('Y-m-d H:i:s')."'";
//        $row = $this->model->exec($sql,[':id'=>$id,':se'=>$se]);
//        $row = $this->model->exec($sql);
        $row = $this->model->getRow($sql);
        if ($row) {
//            dd($row['Session_Data']);
            return $row['session_Data'];
        } else {
            return "";
        }
    }

    public function write($id, $data): bool {
        $DateTime = date('Y-m-d H:i:s');
        $NewDateTime = date('Y-m-d H:i:s', strtotime($DateTime . ' + 1 hour'));
        $sql = "REPLACE INTO session SET Session_Id = '" . $id . "', Session_Expires = '" . $NewDateTime . "', Session_Data = '" . $data . "'";
        $result = $this->model->exec($sql);
//        dd($result);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function destroy($id): bool {
        $sql = "DELETE FROM session WHERE Session_Id ='" . $id . "'";
        $result = $this->model->exec($sql);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function gc($maxlifetime): int|false {
        $sql = "DELETE FROM session WHERE ((UNIX_TIMESTAMP(Session_Expires) + " . $maxlifetime . ") < " . $maxlifetime . ")";
        $result = $this->model->exec($sql);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

}
