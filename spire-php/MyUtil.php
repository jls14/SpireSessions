<?php
//MyUtil.php
class MyUtil
{
	public static function nvl($val1, $val2)
	{
		if(isset($val1) && !is_null($val1) && $val1 != "null"){
			return $val1;
		}
   	 	return $val2;
	}

	public static function coalesce($val1, $val2)
	{
		if(!isset($val1) || is_null($val1)){
			return $val1;
		}
   	 	return $val2;
	}

	public static function get_hash_value($hash, $key){
		if(isset($hash[$key])){
			return $hash[$key];
		}
		return null;
	}

	public static function null_if($val1, $val2){
		if($val1 == $val2){
			return null;
		}
		return $val1;
	}

	public static function isNullOrEmpty($str){
		if(is_null($str) || strlen($str) == 0){
			return true;
		}
		return false;
	}

	public static function formatSqlStringUpload()
	{
		
	}

	public static function isInteger($val)
	{
		return is_integer($val) || preg_match('/^\d{1,}$/', $val);
	}

	public static function isDateFormat($dateStr)
	{
		$pattern = '/^\d{4}-\d{1,2}-\d{1,2}$/';
		return preg_match($pattern, $dateStr);
	}

	public static function isPGBool($bool){
		if($bool){
			return "TRUE";
		}
		return "FALSE";
	}

	public static function fnOk($ok, $reason, $result)
	{
		return ["ok" => $ok, "reason" => $reason, "result" => $result];
	}

	public static function getTimestamp()
	{
		return date('l jS \of F Y h:i:s A');
	}

	public static function parseInt(/*string*/$id){
		if(is_integer($id)){
			return $id;
		}
		elseif(preg_match('/^\d*$/', $id)){
			return intval($id);
		}
		else{
			return null;
		}	
	}

	public static function getDatestring(/*string*/$json_dt){
		//echo $json_dt . "-" . strlen($json_dt);
		if(strlen($json_dt) != 24){
			return null;
		}
		$dateTimeArr = explode('T', $json_dt);
		$timeArr     = explode('.', $dateTimeArr[1]);
		$date_str    = $dateTimeArr[0]." ".$timeArr[0];
		return $date_str;
	}

	public static function myCrypt(/*string*/ $pw){
		if(is_null($pw)){
			return null;
		}
		return crypt($pw,'$6$rounds=5000$'.mt_rand().'$');
	}

	public static function myCryptCheck(/*string*/ $user_input, /*string*/ $hashed_pw){
		return $hashed_pw === crypt($user_input, $hashed_pw);
	}

	private static function hash_equals($str1, $str2) {
	    if(strlen($str1) != strlen($str2)) {
	      return false;
	    } 
	    else {
	      $res = $str1 ^ $str2;
	      $ret = 0;
	      for($i = strlen($res) - 1; $i >= 0; $i--) $ret |= ord($res[$i]);
	      return !$ret;
	    }
  	}

  	public static function prepareSqlString($string) {
  		return str_replace("'", "\'", $string);
  	}
}
?>