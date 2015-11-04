<?php
	//recreate crontab
	class Crontab
	{
		public static function resetCrontab()
		{
			$cron_file = 'cron.txt';
			$cron      = new FileWriter($cron_file, 'a');
			$setSessionPath = '/home1/enderrac/SpirePHP/setSessionWinner.php';

			$stm = "SELECT EXTRACT(MINUTE from end_ts) as minute, " 
			      ."	EXTRACT(HOUR from end_ts) as hour, "
			  	  ."	EXTRACT(day from end_ts) as day_of_month, "
			  	  ."	EXTRACT(MONTH from end_ts) as month, "
			 	  ."	EXTRACT(dow from end_ts) as day_of_week, "
			 	  ."	id "
			 	  ."FROM SPIRE.SESSIONS ";

			$result = ConnDB::query_db($stm);
			
			$fnHash = null;	
			if(!$result){
				$cron->writeLine("ERROR");
				$fnHash = MyUtil::fnOk(false, "SQL Error getting Session Info", null);
			}
			else{ 
				while($row = pg_fetch_assoc($result)){
					$cron->writeLine($row['minute']." ".$row['hour']." ".$row['day_of_month']." ".$row['month']." ".$row['day_of_week']." $setSessionPath ".$row['id']);
				}
				
				$rmCron = shell_exec('crontab -r');
				if($rmCron != null){
					$setCron = shell_exec('crontab $cron_file');
					if($setCron != null){
						$delCronTmp = shell_exec('rm $cron_file');
						if($delCronTmp != null){
							$fnHash = MyUtil::fnOk(true, "Updated Cron and Session", null);
						}
						$fnHash = MyUtil::fnOk(false, "Cron Tmp File not Deleted", null);
					}
					$fnHash = MyUtil::fnOk(false, "Cron not set to tmp", null);
				}
				else{
					$fnHash = MyUtil::fnOk(false, "Original cron not deleted", null);
				}
				return $fnHash;
			}


			return $fnHash;
		}
	}
?>