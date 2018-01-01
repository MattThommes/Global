Global scripts and code for Matt Thommes
==========

This repo contains any global stuff that I re-use from project to project.

## Installation

Using Composer, simply specify what classes you want to use. For example, below I use the `Debug` and `Mysql` classes:

	require "vendor/autoload.php";

	use MattThommes\Debug;
	use MattThommes\Backend\Mysql;

	// Set up object var for Debug.
	$debug = new Debug;

	// Output any variable for debugging purposes.
	$debug->dbg($myvar);

	// Create a function for easier usage.
	function dbg($out, $continue = 0) {
		return $GLOBALS["debug"]->dbg($out, $continue);
	}

	// Set up object var for Mysql.
	$db_conn = new Mysql($mysql_host, $mysql_user, $mysql_pass, $mysql_db);

	// Run a DB query.
	$qry = $db_conn->query("SELECT * FROM ...");

	// Get the query results.
	$rows = $qry->fetch_array();
