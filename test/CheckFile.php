<?php
namespace  test;

class CheckFile
{
    private $logFile;
    private $changeLogPath;
    private $accDir = 'logs/ACC/';

    public function __construct()
    {
        $this->changeLogPath = 'logs/change-log';
        $this->logFile = $this->accDir . date('Y-m-d') . '/' . date('H') . '.log';
    }

    public function accLogCheck()
    {
        $newLog = array();
        $lastTime = $this->getLastTime();
        $finalTime = $this->getFinalTime();
        $lastHour = exec("echo {$lastTime} |awk '{print $2}'| awk -F \":\" '{print $1}'");
        $finalHour = exec("echo {$finalTime} |awk '{print $2}'| awk -F \":\" '{print $1}'");


        //檢查changeLog是否存在並且有資料，否則以最後一筆紀錄重新建立。
        if (!file_exists($this->changeLogPath) || !$lastTime) {
            try {
                if (!$finalTime) {
                    echo "目前無紀錄檔。";
                    return false;
                } elseif (!$lastTime && $finalTime) {
                    self::makeChangeLog($this->changeLogPath, $finalTime);
                    echo "初始化成功";
                }
            } catch (\Exception $e) {
                throw new \Exception($e);
            }
        }
        if ($lastTime == $finalTime) {
            echo "Log無異動資料。";
            return false;
        }
        //判斷是否隔時段
        if ($lastHour != $finalHour) {
            $lastLogPath = $this->accDir . date('Y-m-d') . '/' . $lastHour . '.log';
            $content = $this->getAllLog($lastLogPath);
            $newLog_last = $this->getNewLog($lastTime, $content);
            //若上一個時段無新log則跳過
            if ($newLog_last) {
                $newLog[] = $newLog_last;
            }
            $newLog[] = $this->getAllLog();
        } else {
            $content = $this->getAllLog();
            $newLog[] = $this->getNewLog($lastTime, $content);
        }
            self::makeChangeLog($this->changeLogPath, $finalTime);
            $results = implode(PHP_EOL, $newLog);
            echo "<pre>";
            print_r($results);
    }

    public function getLastTime()
    {
        $lastTime = exec("cat {$this->changeLogPath}");
        if ($lastTime == null) {
            return false;
        }
        return $lastTime;
    }

    public function getFinalTime()
    {
        $finalLogDir = exec("ls {$this->accDir} |tail -n 1");
        $finalLogFile = exec("ls {$this->accDir}{$finalLogDir} |tail -n 1");
        if (empty($finalLogFile)) {
            return false;
        }
        $logFile = $this->accDir . $finalLogDir . '/' . $finalLogFile;
        $finalTime = exec("grep 'active_time' " . $logFile . "|tail -n 1 |awk '{print $2 \" \" $3}'");
        if ($finalTime == null) {
            return false;
        }
        return $finalTime;
    }

    private static function makeChangeLog($path, $finalTime)
    {
        try {
            $file = fopen($path, 'w');
            chmod($path, 0777);
            fwrite($file, $finalTime);
            fclose($file);
            
            return ture;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    public function getNewLog($lastTime, $content)
    {
        preg_match_all('/active_time: ' . $lastTime . '[\s\S]+?out:[\s\S]+?\n([\s\S]+\n)/', $content, $newLog);
        if (empty($newLog[1])) {
            return false;
        }
        return $newLog[1][0];
    }

    public function getAllLog($logPath = null)
    {
        if ($logPath != null && file_exists($logPath)) {
            $fp = fopen($logPath, "r");
        } elseif (file_exists($this->logFile)) {
            $fp = fopen($this->logFile, "r");
        } else {
            return false;
        }
        while (!feof($fp)) {
            $content[] = fgets($fp);
        }
        fclose($fp);
        $content = implode('', $content);

        return $content;

    }
}
