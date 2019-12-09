<?php
/**
 * class for database connection using PDO
 */
class Database{
	// database host
    /**
     * @var PDO
     */
    public static $conn;

    /**
     * @return PDO
     */
    public static function getInstance(){
		if (!isset($conn)) {
			try {
				$ini = parse_ini_file('app.ini');
	        	self::$conn = new PDO('mysql:host=' . $ini['host'] . ';dbname=' . $ini['db_name'], $ini['db_user'], $ini['db_password']);
	        	self::$conn->exec('set names utf8');
	        } catch (PDOException $e) {
	        	echo 'Connection error: ' . $e->getMessage()." in file '".$e->getFile()."' on line ".$e->getLine();
	        }
		}
        return self::$conn;
	}

    /**
     * @param $str string to clean
     * @return string
     */
    static function clean($str){
		$str = trim($str);
		$str = htmlspecialchars($str);
		$str = strip_tags($str);
		return $str;
	}
}