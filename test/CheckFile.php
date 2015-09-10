<?php
namespace  test;

class CheckFile
{
    private $logPath;

    public function __construct()
    {
        $this->logPath = 'logs/change-log';
    }

    public function check()
    {
        $changeLog = array();
        $changeLoad = array();

        exec('stat logs/ACC/' . date('Y-m-d') . '/* |grep -w "File\|Change" ', $changeLoad);
        if (!file_exists($this->logPath)) {
            try {
                $this->makeChangeLog();
            } catch (\Exception $e) {
                throw new \Exception($e);
            }
        }
        exec("cat {$this->logPath}", $changeLog);

        if (!$change = array_diff($changeLog, $changeLoad)) {
            echo "Log資料無異動。";
            return false;
        }

        $arrayKey = array_keys($change)[0] - 1;
        echo "Log資料異動！！";
        var_dump($changeLoad[$arrayKey]);
        $this->makeChangeLog($changeLoad);
    }

    public function makeChangeLog(array $changeLoad)
    {
        $log = fopen($this->logPath, 'w');
        foreach ($changeLoad as $key => $value) {
            fwrite($log, $value . PHP_EOL);
        }
    }
}
