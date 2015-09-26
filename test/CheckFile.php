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

    public function check()
    {
        $lastTime = array();
        $finalTime = array();

        $lastTime = $this->getLastTime();
        $finalTime = $this->getFinalTime();
        $lastHour = exec("echo {$lastTime[0]} |awk '{print $2}'| awk -F \":\" '{print $1}'");
        $finalTime = exec("echo {$finalTime[0]} |awk '{print $2}'| awk -F \":\" '{print $1}'");
        var_dump($lastHour);
        var_dump($finalTime);exit;
        if ($finalTime == null) {
            echo "當前無紀錄。";
            return false;
        }
        if (!file_exists($this->changeLogPath) || empty($lastTime)) {
            try {
                $this->makeChangeLog($finalTime[0]);
            } catch (\Exception $e) {
                throw new \Exception($e);
            }
        }
        if (!$change = array_diff($lastTime, $finalTime)) {
            echo "Log資料無異動。";
            return false;
        }

        echo "Log資料異動！！";

        
        $this->makeChangeLog($finalTime[0]);
        $newLog = $this->getNewLog($lastTime[0]);

        echo "<pre>";
        print_r($newLog);
    }

    public function getFinalTime()
    {
        exec("grep 'active_time' " . $this->logFile . "|tail -n 1 |awk '{print $2 \" \" $3}'", $finalTime);
        if ($finalTime == null) {
            return false;
        }
        return $finalTime;
    }

    public function makeChangeLog($finalTime)
    {
        $file = fopen($this->changeLogPath, 'w');
        chmod($this->changeLogPath, 0777);
        fwrite($file, $finalTime);
        fclose($file);
    }

    public function getLastTime()
    {
        exec("cat {$this->changeLogPath}", $lastTime);
        return $lastTime;
    }

    public function getNewLog($lastTime)
    {
        preg_match_all('/active_time: ' . $lastTime . '[\s\S]+?out:[\s\S]+?\n([\s\S]+\n)/', $content, $newLog);
        return $newLog;
    }

    public function getAllLog()
    {
        $fp = fopen($this->logFile, "r");
        while (!feof($fp)) {
            $content[] = fgets($fp);
        }
        fclose($fp);
        $content = implode('', $content);
    }
}
