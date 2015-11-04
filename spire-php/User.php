<?php
	class User
	{
		private static $VOTER_NBR  = 0;
		private static $ARTIST_NBR = 1;
		private static $ADMIN_NBR  = 2;

		public static function isAdmin(/*int*/$int)
		{
			return $int == self::$ADMIN_NBR;
			
		}

		public static function isArtist(/*int*/$int)
		{
			return $int == self::$ARTIST_NBR;
		}
	}
?>