<?php

class Mysql
{
    var $host;
    var $dbUser;
    var $dbPass;
    var $dbName;
    var $dbConn;
    var $connectError;

    function Mysql( $host, $dbUser, $dbPass, $dbName )
    {
        $this -> host = $host;
        $this -> dbUser = $dbUser;
        $this -> dbPass = $dbPass;
        $this -> dbName = $dbName;
        $this -> connectToDb();
    }

    function connectToDb()
    {
        if ( !$this -> dbConn = @mysql_connect($this -> host, $this -> dbUser, $this -> dbPass) )
        {
            trigger_error("Could not connect to server:" . mysql_error());

            $this -> connectError = true;
        }
        elseif ( !@mysql_select_db($this -> dbName, $this ->dbConn) )
        {
            trigger_error("Could not select database.");

            $this -> connectError = true;
        }
    }

    function isError()
    {
        if ( $this -> connectError )
        {
            return true;
        }

        $error = mysql_error( $this -> dbConn );

        if ( empty($error) )
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    function query($sql)
    {
        if ( !$queryResource = mysql_query($sql, $this -> dbConn) )
        {
            trigger_error("Query failed: " . mysql_error($this -> dbConn) . " SQL: " . $sql);
        }

        return new MysqlResult( $this, $queryResource );
    }

    function close()
    {
    	mysql_close($this -> dbConn);
    }
}

class MysqlResult
{
    var $mysql;
    var $query;

    function MysqlResult( &$mysql, $query )
    {
        $this -> mysql = &$mysql;
        $this -> query = $query;
    }

    function numrows()
    {
        return mysql_num_rows( $this -> query );
    }

    function affectedrows()
    {
        return mysql_affected_rows( $this -> mysql -> dbConn );
    }

    function insertId()
    {
        return mysql_insert_id( $this -> mysql -> dbConn );
    }

    function fetch()
    {
        if ( $row = mysql_fetch_array($this -> query, MYSQL_ASSOC) )
        {
            return $row;
        }
        else if ( $this -> numrows() > 0 )
        {
            mysql_data_seek( $this -> query, 0 );

            return false;
        }
        else
        {
            return false;
        }
    }

    function fetch_array($key = null, $field = null, $key_function = null)
    {
    	$rows = array();

    	while ($row = mysql_fetch_array($this -> query, MYSQL_ASSOC))
    	{
    		$record = ($field) ? $row[$field] : $row;

				if ($key)
				{
					// USE THE SPECIFIED FIELD VALUE AS THE STRING KEY.

					// IF THE SPECIFIED FIELD VALUE NEEDS A FUNCTION APPLIED TO IT, DO SO. IF NOT, LEAVE IT ALONE.
					$key = ($key_function) ? $key_function($row[$key]) : $row[$key];

					$rows[ $key ] = $record;
				}
				else
				{
					// OR LEAVE IT AS STANDARD NUMERIC KEY.
    			$rows[] = $record;
    		}
    	}

    	return $rows;
    }

    function isError()
    {
    	return $this -> mysql -> isError();
    }
}

?>