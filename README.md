phpMysqlConnector
=================

Sample PHP Mysql connector class
This classes are used to abstract the connection to a Mysql database

Sample
=========
		$settings = array(
						'db' => array(
										'host' => 'localhost',
										'pass' => 'password',
										'username' => 'username',
										'db_name' => 'name',
										'type' => 'mysql',
										'error_show' => true),
						
						);
		include 'class.$settings['db']['type'].php';
		$db = new Database($settings['db']);
		$rs = new RecordSet($db->db);
		
		$rs->select('table','field1,field2','id=2','field3 asc');
		$row = $rs->fetch();
		echo $row->field1 . "<br>" . $row->field2;