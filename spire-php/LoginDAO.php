<?php
//ArtistDAO.php
class LoginDAO
{
	private static $table_name = 'spire.LOGIN_TOKENS';
	private static $ADMIN_SESSION = -99;
	public static function getUserFromToken($token)
	{
		$stm = "SELECT USER_ID "
			  ."FROM ".self::$table_name." "
			  ."WHERE IS_ACTIVE = TRUE ";

		$result = ConnDB::query_db($stm);
		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}

		$row = pg_fetch_assoc($result);
		if(isset($row['user_id']) && is_numeric($row['user_id'])){
			return MyUtil::fnOk(true, "Found User From Token", $row['user_id']);
		}
		return MyUtil::fnOk(false, "User Not Found, Invalid Token", null);
	}
	
	public static function getAndSaveToken($usernameOrEmail, $pw_hash)
	{
		$fnHash = self::authAndGetId($usernameOrEmail, $pw_hash);
		
		//if error during authentication, return fnHash
		if(!$fnHash['ok']){
			return $fnHash;
		}

		$user_id = $fnHash['result'];
		$token   = self::createToken($user_id);
		self::saveToken($user_id, $token, self::$ADMIN_SESSION);

		return MyUtil::fnOk(true, "Token Saved and Created", $token);
	}

	public static function getAndSaveTokenFB($spireId, $sessionId)
	{

		$token 	   = self::createToken($spireId);
		$tokenHash = self::saveToken($spireId, $token, $sessionId);
		if($tokenHash['ok'])
		{
			return MyUtil::fnOk(true, "Token Saved", $token);
		}
		return MyUtil::fnOk(false, "SQL Error getting token", null);
	}

	private static function authAndGetId($usernameOrEmail, $password)
	{
		if(MyUtil::isNullOrEmpty($usernameOrEmail))
		{
			return MyUtil::fnOk(false, "Must have email or username", null);
		}
		elseif(MyUtil::isNullOrEmpty($password))
		{
			return MyUtil::fnOk(false, "Must have password", null);	
		}

		$stm = "SELECT ID, PASSWORD FROM SPIRE.USERS WHERE USERNAME = '$usernameOrEmail' OR EMAIL = '$usernameOrEmail'";
		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}
		
		$row = pg_fetch_assoc($result);
		if(isset($row['id']) && !is_null($row['id'])){
			if(MyUtil::myCryptCheck($password, $row['password'])){
				return MyUtil::fnOk(true, "User Authenticated", $row['id']);
			}
			else{
				return MyUtil::fnOk(false, "Incorrect Password", null);
			}
		}
		return MyUtil::fnOk(false, "Incorrect Username or Email and Password pairing", null);
	}

	public static function getTokenData($token){
		if(MyUtil::isNullOrEmpty($token)){
			return MyUtil::fnOk(false, "Must have a token", null);
		}

		$stm = "SELECT tok.USER_ID, use.USER_TYPE_NBR, tok.SESSION_ID "
			  ." FROM ".self::$table_name." tok " 
			  ." JOIN spire.USERS use ON tok.USER_ID = use.ID "
			  ." WHERE TOKEN_ID = '$token' ";
		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}
		$row = pg_fetch_assoc($result);
		if(isset($row['user_id'])){
			return MyUtil::fnOk(true, "User-Token found", $row);
		}
	}

	private static function saveToken($user_id, $token, $session_id)
	{
		$stm = "INSERT INTO ".self::$table_name."(USER_ID, TOKEN_ID, SESSION_ID) VALUES ('$user_id', '$token', CAST(COALESCE(nullIf('$session_id',''),'0') as integer))";
		$result = ConnDB::query_db($stm);
		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}
		return MyUtil::fnOk(true, "Token Inserted", $result);
	}

	private static function createToken($user_id)
	{
		return "$user_id".time();
	}
}
?>