<?php
//MyLog.php
class FileWriter
{
	private $logfile;
	
	function __construct($file, $type)
	{
		if($type != 'a'){
			$type = 'w';
		}
		$this->logFile = fopen($file, $type);
	}

	public function writeLog($string)
	{
		$log = self::getTimestamp() . " : " . $string . "\n";
		fwrite($this->logFile, $log);
	}

	public function writeLine($string)
	{
		fwrite($this->logFile, $string);
	}

	private static function getTimestamp()
	{
		return date('l jS \of F Y h:i:s A');
	}
}
?>