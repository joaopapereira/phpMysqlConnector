<?php

/* vim: set expandtab tabstop=4 shiftwidth=4: */

class Database
{
	/*
	*	@var connection to db
	*/
	var $db;
	/*
	*	@var database name
	*/
	var $bd_name;
	/*
	*	@var database host
	*/
	var $bd_host;
	/*
	*	@var databse username
	*/
	var $bd_username;
	/*
	*	@var Database password
	*/
	var $bd_pass;
	/*
	*	@var show or hide the error from final user
	*/
	var $s_error=false;
	
	/**
	*	@desc Constructor of Database class that initialize the connection to the db
	*	@param boolean $show_error	Show or hide error from user
	*	@param string $dname		Database name
	*	@param string $dpass		Database password
	*	@param string $dusername	Database username
	*	@param string $dhost		Database hostname
	*	@param array  $setting		Array with the database settings
	
	* 	@return Database
	*/
	function Database($setting = '', $dname = '', $dpass = '', $dusername = '',$dhost='localhost',$show_error = true)
	{
		if (is_array($setting) )
		{
			$this->bd_username = $setting['username'];
			$this->bd_pass = $setting['pass'];
			$this->bd_host = $setting['host'];
			$this->bd_name = $setting['db_name'];
			$this->s_error = strlen($setting['error_show'])>0?$setting['error_show']:false;
			$this->_connect($this->bd_host,$this->bd_username,$this->bd_pass);
			$this->_choose_db($this->bd_name);

		}
		else
		{
			if( strlen($dusername) > 0 && strlen($dpass) > 0 && strlen($dname) > 0 )
			{
				$this->bd_username=$dusername;
				$this->bd_pass=$dpass;
				$this->bd_host=$dhost;
				$this->bd_name=$dname;
				$this->s_error=$show_error;
				$this->_connect($this->bd_host,$this->bd_username,$this->bd_pass);
				$this->_choose_db($this->bd_name);
			}
			//else
				//$this->error('Did not introduced all the settings necessary');
		}
	}
	/**
	*	@desc Put database settings in Constant values
	*	@param string $duser		Database username constante
	*	@param string $dpass		Database password constante
	*	@param string $dhost		Database hostname constante
	*	@param string $dname		Database name constante
	*
	*	@return void
	*/
	function put_settings_in_defines($duser,$dpass,$dhost,$dname)
	{
		define($duser,$this->bd_username);
		define($dpass,$this->bd_pass);
		define($dhost,$this->bd_host);
		define($dname,$this->bd_name);
	}
	/**
	*	@desc Put the value of the connection in a constant so it can be used in the class Recordset
	*	@param string $name		Name of the constant
	
	* @return void
	*/
	function put_bdconid_define($name)
	{
		define($name,$this->db);
	}
	
	/**
	* @return void
	* @param unknown $bd
	* @desc This function close the connection to the database
	*/
	function close($bd = '')
	{
		if(strlen($bd ) > 0)
			mysql_close($bd->db);
		else
			mysql_close($this->db);
	}
	//Private methods
	/**
	*	@desc Connects to the mysql_server
	*	@param string $dbhost Hostname of the Mysql Server
	*	@param string $dbuser Username of the Mysql Server
	*	@param string $dbpass Password of the Mysql Server	
	
	* @return boolean
	*/
	function _connect($dbhost,$dbuser,$dbpass)
	{
		@$this->db = mysql_connect($dbhost,$dbuser,$dbpass);
		if(!$this->db)
			$this->error("Could not connect to the Mysql Server");
		else
			return true;
	}
	
	/**
	*	@desc Choose the database
	*	@param string $dbname Name of the database
	
	* @return void
	*/
	function _choose_db($dbname)
	{
		$openned=mysql_select_db($dbname);
		if(!$openned)
			$this->error('Error choosing the database');
	}
	/**
	*	@desc Error handler function
	*	@param string $description		the error descrition
	*	@param string $file				The name of the file where error ocurred
	*	@param integer $line			The line where the error ocurred
	
	* @return void
	*/
	function error($description, $file='', $line='',$error = '')
    {
		if( strlen($error) == 0 )
			$error = $this->s_error;
			
		if(!$error)
			die("An error ocurred. These are the details:<br />File: <strong>{$file}</strong><br />Line: <strong>{$line}</strong><br />Description: <strong>{$description}</strong>");
		else
			die("An error ocurred during a database action, please try again later");
    }


}
class RecordSet 
{
	/*
	*	@var connection to db
	*/
	var $db;
	/*
	*	@var mixed (error)
	*/
	var $error=true;
	/*
	*	@var object
	*/
	var $result;
	/*
	*	@var int number of rows in a query
	*/
	var $num_rows;
	
	var $lastID;
	
	var $dbObj;
	
	/**
	* @return RecordSet
	* @param unknown $link
	* @desc Contructor initialze the link to the database
	*/
	function RecordSet($link,$dbObj = '')
	{
		$this->db=$link;
		if( strlen($dbObj)>0 ){
			$this->dbObj = $dbObj;
			$this->error = $dbObj->s_error;
		}
	}
	/**
	*	@desc Do select some fields from a row of a table
	*	@param string $tblname Name of the table
	*	@param array of strings $fields fields you whant to select
	*	@param strings $filter The restrictions to the search
	*	@param strings $order To order the search by the option here
	*	@param strings $group To group the search by the option
	*	@param string $limits Put the limit in the search
	*
	*	@sample select_query("table",,"field1>5 AND field2<4","field asc","field1",)
	*			sql== SELECT  FROM table WHERE field1>5 AND field2<4 ORDER BY field ASC GROUP BY field1 LIMITS 1,6
	*	
	*	@return boolean
	*	@return die and write and error
	*	@return the select from the page
	
	*/
	function select($tblname,$fields='*',$filter='',$order='',$group='',$limits='')
	{
		$sql='SELECT ';
		$sql.=$fields;
		$sql.=' FROM '.$tblname;
		if($filter!='')
			$sql.=' WHERE '.$filter;
		if($order!='')
			$sql.=' ORDER BY '.$order;
		if($group!='')
			$sql.=' GROUP BY '.$group;
		if($limits!='')
			$sql.=" LIMIT ".$limits;

		$this->result=mysql_query($sql,$this->db);
		@$this->num_rows = mysql_num_rows($this->result);
		if(!$this->result)
			Database::error("Failure: mysql_query( $sql ),".mysql_error($this->db),$_SERVER["SCRIPT_NAME"],$this->error);
		else
			return true;
	}
	/**
	*	@desc Do update some fields from a row of a table
	*	@param string $tblname Name of the table
	*	@param string $fields fields you whant to update
	*	@param string $values values to update the fields
	*	@param string $filter The restrictions to the search
	*	@param string $limits Put the limit in the search
	*
	*	@sample update("table","field1=4,$field2='abc'","field3=10","1,10")
	*			sql== UPDATE table SET field1=4,$field2='abc' WHERE field3=10 LIMIT 1,10
	*
	*	@return boolean
	*	@return die and write and error
	*	@return the select from the page
	
	*/
	function update($tblname,$fields_values,$filter='',$limits='')
	{
		$sql='UPDATE '.$tblname.' SET ';
		
		$sql.=$fields_values;
		if($filter!='')
			$sql.=" WHERE ".$filter;
		if($limits!='')
			$sql.=" LIMIT ".$limits;
		$this->result=mysql_query($sql,$this->db);
		@$this->num_rows = mysql_num_rows($this->result);
		if(!$this->result)
			Database::error("Failure: mysql_query( $sql ),".$this->db,$_SERVER["SCRIPT_NAME"],$this->error);
		else
			return $this->result;
	}
	/**
	*	@desc Insert some rows from a table
	*	@param string $tblname Name of the table
	*	@param string $fields fields you whant to update
	*	@param string $values values to update the fields
	*
	*	@sample update("table",)
	*			sql== INSERT INTO table(field1,field2) VALUES(4,'abc')
	*
	*	@return boolean
	*	@return die and write and error
	*	@return the select from the page
	
	*/
	function insert($tblname,$fields,$values)
	{
		$sql='INSERT INTO '.$tblname.'(';
		$sql.=$fields.') VALUES('.$values.')';
		$this->result=mysql_query($sql,$this->db);
		@$this->num_rows = mysql_num_rows($this->result);
		@$this->lastID = mysql_insert_id();
		if(!$this->result)
			$this->dbObj->error("Failure: mysql_query( $sql )".mysql_error($this->db),$_SERVER["SCRIPT_NAME"],$this->error);
		else
			return $this->result;
	}
	/**
	*	@desc Delete some rows from a table
	*	@param string $tblname Name of the table
	*	@param string $filter The restrictions to the delete
	*	@param string $limit Put the limit in the delter
	*
	*	@sample update("table","field1=5","1,3")
	*			sql= DELETE FROM table WHERE field1=5 LIMIT 1,3
	*
	*	@return boolean
	*	@return die and write and error
	*	@return the select from the page
	
	*/
	function delete($tblname,$filter='',$limit='')
	{
		$sql="DELETE FROM $tblname";
		if($filter!='')
			$sql.=" WHERE ".$filter;
		if($limit!='')
			$sql.=" LIMIT ".$limit;
		$this->result=mysql_query($sql,$this->db);
		@$this->num_rows = mysql_num_rows($this->result);
		if(!$this->result)
			Database::error("Failure: mysql_query( $sql )".mysql_error($this->db),$_SERVER["SCRIPT_NAME"],$this->error);
		else
			return $this->result;
	}
	/**
	*	@desc Do fetch of the mysql statment
	*	@return variable fetch value
	
	*/
	function fetch()
	{
	  	$ret = @mysql_fetch_object($this->result);
      	return $ret;
	}
	/**
	*	@desc Count number os row in a query
	*	@return int return number of rows
	
	*/
	function num_rows()
	{
		return $this->num_rows;
	}
	/**
	*	@desc Create a table in a database
	*	@param string $tblname	name of the new table
	*	@param string $fields	the fields to add in this new table
	*	
	*	@return boolean
	*	@return die and write and error
	*	@return the result fo the query
	
	*/
	function add_table($tblname,$fields)
	{
		$sql='CREATE TABLE '.$tblname;
		if(strlen($fields) > 0)
			$sql.= ' '.$fields;
		$this->result=mysql_query($sql,$this->db);
		@$this->num_rows = mysql_num_rows($this->result);
		if(!$this->result)
			Database::error("Failure: mysql_query( $sql )".mysql_error($this->db),$_SERVER["SCRIPT_NAME"],$this->error);
		else
			return $this->result;
	}
	/**
	*	@desc Deletes a table
	*	@param string $tblname	name of the table to delete
	*	
	*	@return boolean
	*	@return die and write and error
	*	@return the result fo the query
	
	*/
	function drop_table($tblname)
	{
		$sql="DROP TABLE ".$tblname;
		$this->result=mysql_query($sql,$this->db);
		@$this->num_rows = mysql_num_rows($this->result);
		if(!$this->result)
			Database::error("Failure: mysql_query( $sql )".mysql_error($this->db),$_SERVER["SCRIPT_NAME"],$this->error);
		else
			return $this->result;
	}
	/**
	*	@desc Edit one existing table
	*	@param string $tblname		name of the table
	*	@param string $add_field	fields and there atributes that are going to be added in the table
	*	@param string $modify_field	fields and there atributes that are going to be modified in the table
	*	@param string $drop_field	fields and there atributes that are going to be deleted in the table
	*
	*	@return boolean
	*	@return die and write and error
	*	@return the result fo the query
	
	*/
	function edit_table($tblname,$add_field='',$modify_field='',$drop_field='')
	{
		$sql="ALTER TABLE ".$tblname;
		if(strlen($add_field) > 0)
			$sql.=' ADD '.$add_field;
		if(strlen($modify_field) > 0 )
			$sql.=' MODIFY '.$modify_field;
		if(strlen($drop_field) > 0 )
			$sql.=' DROP '.$drop_field;
		$this->result=mysql_query($sql,$this->db);
		@$this->num_rows = mysql_num_rows($this->result);
		if(!$this->result)
			Database::error("Failure: mysql_query( $sql )".mysql_error($this->db),$_SERVER["SCRIPT_NAME"],$this->error);
		else
			return $this->result;
	}
	/**
	*	@desc Clear values in the recordset
	*	@param RecordSet $rs		a rescordeset you whant to free
	*
	
	* @return void
	*/
	function free($rs = '')
	{
		if(strlen($rs ) > 0 )
		{
			mysql_free_result($rs->result);
			$rs->num_rows = 0;
		}
		else
		{
			mysql_free_result($this->result);
			$this->num_rows = 0;
		}
	}
	/**
	* @return variable query result
	* @param unknown $sql
	* @desc If whant to use sql directtly this is the fun
	*/
	function query($sql)
	{
		$this->result=mysql_query($sql,$this->db);
		@$this->num_rows = mysql_num_rows($this->result);
		
		if($this->result === false)
			Database::error("Failure: mysql_query( $sql )".mysql_error($this->db),$_SERVER["SCRIPT_NAME"],$this->error);
		else
			return $this->result;
	}
	
	/**
	* @return integer ID
	* @desc Returns the id generated from the last insert made with this object
	*/
	function lastIdInserted(){
		return $this->lastID;
	}
}
?>