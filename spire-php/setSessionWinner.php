<?php
	//EndSession.php
	require('/home1/enderrac/SpirePHP/FileWriter.php');
	require('/home1/enderrac/SpirePHP/ConnDB.php');
	require('/home1/enderrac/SpirePHP/MyUtil.php');
	require('/home1/enderrac/SpirePHP/FileWriter.php')

	$logger = new FileWriter('cron_log', 'a');

	$logger->writeLog('Start CRON');

	$stm = " SELECT id FROM spire.SESSIONS WHERE to_char(end_ts, 'yyyy-mm-dd') = to_char(CURRENT_DATE, 'yyyy-mm-dd') ";

	$result = ConnDB::query_db($stm);

	while($row = pg_fetch_assoc($result)){
		$session_id = $row['id'];
		$logger->writeLog('    Updating Session $session_id');

		//update vids
		$stm = " UPDATE spire.VIDEOS "
		      ." SET VOTES = (SELECT COUNT(*) FROM votes WHERE session_id = $session_id) "
		      ." WHERE session_id = $session_id "
		      ."   AND to_char((SELECT end_ts from sessions where id = $session_id), 'yyyy-mm-dd') = to_char(CURRENT_DATE, 'yyyy-mm-dd') ";

		$result = ConnDB::query_db($stm);
		if(!$result){
			//update session winner vid
			$stm = " UPDATE spire.SESSIONS "
		      	  ." SET winner_video_id = (SELECT id FROM videos WHERE session_id = $session_id AND votes = (SELECT MAX(votes) FROM videos WHERE session_id = $session_id))"
		      	  ." WHERE session_id = $session_id "
		      	  ." 	AND to_char(end_ts, 'yyyy-mm-dd') = to_char(CURRENT_DATE, 'yyyy-mm-dd') ";

			$result = ConnDB::query_db($stm);
			if(!$result){
				$logger->writeLog('    Updates Completed');
			}
			else{
				$logger->writeLog('    Update SESSIONS failed: $stm');
			}
		}
		else{
			$logger->writeLog('    Update Videos failed: $stm');
		}
	}
	$logger->writeLog('End CRON');
?>