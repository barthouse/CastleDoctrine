<?php



global $cd_version;
$cd_version = "1";



// edit settings.php to change server' settings
include( "settings.php" );




// no end-user settings below this point



// no caching
//header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache'); 



// enable verbose error reporting to detect uninitialized variables
error_reporting( E_ALL );



// page layout for web-based setup
$setup_header = "
<HTML>
<HEAD><TITLE>Castle Doctrine Server Web-based setup</TITLE></HEAD>
<BODY BGCOLOR=#FFFFFF TEXT=#000000 LINK=#0000FF VLINK=#FF0000>

<CENTER>
<TABLE WIDTH=75% BORDER=0 CELLSPACING=0 CELLPADDING=1>
<TR><TD BGCOLOR=#000000>
<TABLE WIDTH=100% BORDER=0 CELLSPACING=0 CELLPADDING=10>
<TR><TD BGCOLOR=#EEEEEE>";

$setup_footer = "
</TD></TR></TABLE>
</TD></TR></TABLE>
</CENTER>
</BODY></HTML>";






// ensure that magic quotes are on (adding slashes before quotes
// so that user-submitted data can be safely submitted in DB queries)
if( !get_magic_quotes_gpc() ) {
    // force magic quotes to be added
    $_GET     = array_map( 'cd_addslashes_deep', $_GET );
    $_POST    = array_map( 'cd_addslashes_deep', $_POST );
    $_REQUEST = array_map( 'cd_addslashes_deep', $_REQUEST );
    $_COOKIE  = array_map( 'cd_addslashes_deep', $_COOKIE );
    }
    





// all calls need to connect to DB, so do it once here
cd_connectToDatabase();

// close connection down below (before function declarations)


// testing:
//sleep( 5 );


// general processing whenver server.php is accessed directly




// grab POST/GET variables
$action = "";
if( isset( $_REQUEST[ "action" ] ) ) {
    $action = $_REQUEST[ "action" ];
    }

$debug = "";
if( isset( $_REQUEST[ "debug" ] ) ) {
    $debug = $_REQUEST[ "debug" ];
    }

$remoteIP = "";
if( isset( $_SERVER[ "REMOTE_ADDR" ] ) ) {
    $remoteIP = $_SERVER[ "REMOTE_ADDR" ];
    }




if( $action == "version" ) {
    global $cd_version;
    echo "$cd_version";
    }
else if( $action == "show_log" ) {
    cd_showLog();
    }
else if( $action == "clear_log" ) {
    cd_clearLog();
    }
else if( $action == "check_user" ) {
    cd_checkUser();
    }
else if( $action == "start_edit_house" ) {
    cd_startEditHouse();
    }
else if( $action == "end_edit_house" ) {
    cd_startEditHouse();
    }
else if( $action == "show_data" ) {
    cd_showData();
    }
else if( $action == "show_detail" ) {
    cd_showDetail();
    }
else if( $action == "cd_setup" ) {
    global $setup_header, $setup_footer;
    echo $setup_header; 

    echo "<H2>Castle Doctrine Server Web-based Setup</H2>";

    echo "Creating tables:<BR>";

    echo "<CENTER><TABLE BORDER=0 CELLSPACING=0 CELLPADDING=1>
          <TR><TD BGCOLOR=#000000>
          <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=5>
          <TR><TD BGCOLOR=#FFFFFF>";

    cd_setupDatabase();

    echo "</TD></TR></TABLE></TD></TR></TABLE></CENTER><BR><BR>";
    
    echo $setup_footer;
    }
else if( preg_match( "/server\.php/", $_SERVER[ "SCRIPT_NAME" ] ) ) {
    // server.php has been called without an action parameter

    // the preg_match ensures that server.php was called directly and
    // not just included by another script
    
    // quick (and incomplete) test to see if we should show instructions
    global $tableNamePrefix;
    
    // check if one of our table exists
    $tableName = $tableNamePrefix . "users";
    
    $exists = cd_doesTableExist( $tableName );
        
    if( $exists  ) {
        echo "Castle Doctrine Server database setup and ready";
        }
    else {
        // start the setup procedure

        global $setup_header, $setup_footer;
        echo $setup_header; 

        echo "<H2>Castle Doctrine Server Web-based Setup</H2>";
    
        echo "Server will walk you through a " .
            "brief setup process.<BR><BR>";
        
        echo "Step 1: ".
            "<A HREF=\"server.php?action=cd_setup\">".
            "create the database tables</A>";

        echo $setup_footer;
        }
    }



// done processing
// only function declarations below

cd_closeDatabase();







/**
 * Creates the database tables needed by seedBlogs.
 */
function cd_setupDatabase() {
    global $tableNamePrefix;

    $tableName = $tableNamePrefix . "log";
    if( ! cd_doesTableExist( $tableName ) ) {

        $query =
            "CREATE TABLE $tableName(" .
            "entry TEXT NOT NULL, ".
            "entry_time DATETIME NOT NULL );";

        $result = cd_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";
        }
    else {
        echo "<B>$tableName</B> table already exists<BR>";
        }


    
    $tableName = $tableNamePrefix . "users";
    if( ! cd_doesTableExist( $tableName ) ) {

        // this table contains general info about each user
        // unique ID is ticket ID from ticket server
        //
        // sequence number used and incremented with each client request
        // to prevent replay attacks
        $query =
            "CREATE TABLE $tableName(" .
            "user_id INT NOT NULL PRIMARY KEY AUTO INCREMENT," .
            "ticket_id CHAR(10) NOT NULL," .
            "sequence_number INT NOT NULL," .
            "blocked TINYINT NOT NULL );";

        $result = cd_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";
        }
    else {
        echo "<B>$tableName</B> table already exists<BR>";
        }

    
    
    $tableName = $tableNamePrefix . "houses";
    if( ! cd_doesTableExist( $tableName ) ) {

        // this table contains general info about each user's house
        // EVERY user has EXACTLY ONE house
        $query =
            "CREATE TABLE $tableName(" .
            "user_id INT NOT NULL PRIMARY KEY," .
            "character_name TEXT NOT NULL," .
            "house_map LONGTEXT NOT NULL," .
            "loot_value INT NOT NULL," .
            "edit_checkout TINYINT NOT NULL,".
            "rob_checkout TINYINT NOT NULL,".
            "last_ping_time DATETIME NOT NULL,".
            "blocked TINYINT NOT NULL ) ENGINE = INNODB;";

        $result = cd_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";
        }
    else {
        echo "<B>$tableName</B> table already exists<BR>";
        }

    }



function cd_showLog() {
    $password = cd_checkPassword( "show_log" );

     echo "[<a href=\"server.php?action=show_data&password=$password" .
         "\">Main</a>]<br><br><br>";
    
    global $tableNamePrefix;

    $query = "SELECT * FROM $tableNamePrefix"."log ".
        "ORDER BY entry_time DESC;";
    $result = cd_queryDatabase( $query );

    $numRows = mysql_numrows( $result );



    echo "<a href=\"server.php?action=clear_log&password=$password\">".
        "Clear log</a>";
        
    echo "<hr>";
        
    echo "$numRows log entries:<br><br><br>\n";
        

    for( $i=0; $i<$numRows; $i++ ) {
        $time = mysql_result( $result, $i, "entry_time" );
        $entry = mysql_result( $result, $i, "entry" );

        echo "<b>$time</b>:<br>$entry<hr>\n";
        }
    }



function cd_clearLog() {
    $password = cd_checkPassword( "clear_log" );

     echo "[<a href=\"server.php?action=show_data&password=$password" .
         "\">Main</a>]<br><br><br>";
    
    global $tableNamePrefix;

    $query = "DELETE FROM $tableNamePrefix"."log;";
    $result = cd_queryDatabase( $query );
    
    if( $result ) {
        echo "Log cleared.";
        }
    else {
        echo "DELETE operation failed?";
        }
    }






function cd_checkUser() {
    global $tableNamePrefix, $ticketServerNamePrefix;

    $email = "";
    if( isset( $_REQUEST[ "email" ] ) ) {
        $level_number = $_REQUEST[ "email" ];
        }

    $query = "SELECT * FROM $ticketServerNamePrefix"."tickets ".
            "WHERE email = '$email';";
    $result = ts_queryDatabase( $query );
    
    $numRows = mysql_numrows( $result );

    if( $numRows != 1 ) {
        echo "DENIED";
        return;
        }
    
    
    $row = mysql_fetch_array( $result, MYSQL_ASSOC );
    
    $ticket_id = $row[ "ticket_id" ];
    $blocked = $row[ "blocked" ];

    if( $blocked ) {
        echo "DENIED";
        return;
        }

    
    $query = "SELECT * FROM $tableNamePrefix"."users ".
        "WHERE ticket_id = '$ticket_id';";
    $result = ts_queryDatabase( $query );
    
    $numRows = mysql_numrows( $result );

    $user_id;
    $sequence_number;
    
    if( $numRows < 1 ) {
        // new user, in ticket server but not here yet

        // create

        // user_id auto-assigned
        $query = "INSERT INTO $tableNamePrefix"."users ".
            "(ticket_id, sequence_number, blocked) VALUES(" .
            " '$ticket_id', 0, 0 );" 
        $result = ts_queryDatabase( $query );

        $user_id = mysql_insert_id();
        $sequence_number = 0;


        // create default house for user
        /*
"user_id INT NOT NULL PRIMARY KEY," .
            "character_name TEXT NOT NULL," .
            "house_map LONGTEXT NOT NULL," .
            "loot_value INT NOT NULL," .
            "edit_checkout TINYINT NOT NULL,".
            "rob_checkout TINYINT NOT NULL,".
            "last_ping_time DATETIME NOT NULL,".
            "blocked TINYINT NOT NULL );";
        */

        // FIXME:  generate a unique name here
        $character_name = "Sam Jacob Smith";
        
        $query = "INSERT INTO $tableNamePrefix"."users VALUES(" .
            " $user_id, $character_name, 1000, 0, 0, CURRENT_TIMESTAMP, 0 );";
        $result = ts_queryDatabase( $query );
        }
    else {
        $row = mysql_fetch_array( $result, MYSQL_ASSOC );
    
        $blocked = $row[ "blocked" ];

        if( $blocked ) {
            echo "DENIED";
            return;
            }

        $user_id = $row[ "user_id" ];
        $sequence_number = $row[ "sequence_number" ];
        }
    
    echo "$user_id $sequence_number";
    
    
    }











function cd_showData() {
    $password = cd_checkPassword( "show_data" );

    global $tableNamePrefix, $remoteIP;
    

    echo "[<a href=\"server.php?action=show_data&password=$password" .
            "\">Main</a>]<br><br><br>";




    $skip = 0;
    if( isset( $_REQUEST[ "skip" ] ) ) {
        $skip = $_REQUEST[ "skip" ];
        }

    global $housesPerPage;
    
    $search = "";
    if( isset( $_REQUEST[ "search" ] ) ) {
        $search = $_REQUEST[ "search" ];
        }

    $keywordClause = "";
    $searchDisplay = "";
    
    if( $search != "" ) {
        

        $keywordClause = "WHERE ( user_id LIKE '%$search%' " .
            "OR character_name LIKE '%$search%' ".
            "OR loot_value LIKE '%$search%' ) ";

        $searchDisplay = " matching <b>$search</b>";
        }
    

    

    // first, count results
    $query = "SELECT COUNT(*) FROM $tableNamePrefix"."houses $keywordClause;";

    $result = cd_queryDatabase( $query );
    $totalHouses = mysql_result( $result, 0, 0 );

    
             
    $query = "SELECT * FROM $tableNamePrefix"."houses $keywordClause".
        "ORDER BY last_download_date DESC ".
        "LIMIT $skip, $housesPerPage;";
    $result = cd_queryDatabase( $query );
    
    $numRows = mysql_numrows( $result );

    $startSkip = $skip + 1;
    
    $endSkip = $startSkip + $housesPerPage - 1;

    if( $endSkip > $totalHouses ) {
        $endSkip = $totalHouses;
        }



        // form for searching houses
?>
        <hr>
            <FORM ACTION="server.php" METHOD="post">
    <INPUT TYPE="hidden" NAME="password" VALUE="<?php echo $password;?>">
    <INPUT TYPE="hidden" NAME="action" VALUE="show_data">
    <INPUT TYPE="text" MAXLENGTH=40 SIZE=20 NAME="search"
             VALUE="<?php echo $search;?>">
    <INPUT TYPE="Submit" VALUE="Search">
    </FORM>
        <hr>
<?php

    

    
    echo "$totalHouses active houses". $searchDisplay .
        " (showing $startSkip - $endSkip):<br>\n";

    
    $nextSkip = $skip + $housesPerPage;

    $prevSkip = $skip - $housesPerPage;
    
    if( $prevSkip >= 0 ) {
        echo "[<a href=\"server.php?action=show_data&password=$password" .
            "&skip=$prevSkip&search=$search\">Previous Page</a>] ";
        }
    if( $nextSkip < $totalHouses ) {
        echo "[<a href=\"server.php?action=show_data&password=$password" .
            "&skip=$nextSkip&search=$search\">Next Page</a>]";
        }

    echo "<br><br>";
    
    echo "<table border=1 cellpadding=5>\n";

    echo "<tr><td>User ID</td>\n";
    echo "<td>Blocked?</td>\n";
    echo "<td>Character Name</td>\n";
    echo "<td>Loot Value</td>\n";
    echo "<td>Checkout?</td>\n";
    echo "<td>Ping Time</td>\n";
    

    for( $i=0; $i<$numRows; $i++ ) {
        $user_id = mysql_result( $result, $i, "user_id" );
        $character_name = mysql_result( $result, $i, "character_name" );
        $loot_value = mysql_result( $result, $i, "loot_value" );
        $edit_checkout = mysql_result( $result, $i, "edit_checkout" );
        $rob_checkout = mysql_result( $result, $i, "rob_checkout" );
        $last_ping_time = mysql_result( $result, $i, "last_ping_time" );
        $blocked = mysql_result( $result, $i, "blocked" );
        
        $block_toggle = "";
        
        if( $blocked ) {
            $blocked = "BLOCKED";
            $block_toggle = "<a href=\"server.php?action=block_user_id&".
                "blocked=0&user_id=$user_id&password=$password\">unblock</a>";
            
            }
        else {
            $blocked = "";
            $block_toggle = "<a href=\"server.php?action=block_user_id&".
                "blocked=1&user_id=$user_id&password=$password\">block</a>";
            
            }

        $checkout = "";

        if( $edit_checkout ) {
            $checkout = "edit";
            }
        if( $rob_checkout ) {
            $checkout = "rob";
            }
        
        

        
        echo "<tr>\n";
        
        echo "<td><b>$user_id</b>";
        echo "[<a href=\"server.php?action=show_detail&password=$password" .
            "&user_id=$user_id\">detail</a>]</td>\n";
        echo "<td align=right>$blocked [$block_toggle]</td>\n";
        echo "<td>$character_name</td>\n";
        echo "<td>$loot_value</td>\n";
        echo "<td>$checkout</td>\n";
        echo "<td>$last_ping_time</td>\n";
        echo "</tr>\n";
        }
    echo "</table>";


    echo "<hr>";


    echo "<a href=\"server.php?action=show_log&password=$password\">".
        "Show log</a>";
    echo "<hr>";
    echo "Generated for $remoteIP\n";

    }



function cd_showDetail() {
    $password = cd_checkPassword( "show_detail" );

     echo "[<a href=\"server.php?action=show_data&password=$password" .
         "\">Main</a>]<br><br><br>";
    
    global $tableNamePrefix;

    // FIXME:  anything needed here?

    echo "Does nothing for now";    
    }










// general-purpose functions down here, many copied from seedBlogs

/**
 * Connects to the database according to the database variables.
 */  
function cd_connectToDatabase() {
    global $databaseServer,
        $databaseUsername, $databasePassword, $databaseName;
    
    
    mysql_connect( $databaseServer, $databaseUsername, $databasePassword )
        or cd_fatalError( "Could not connect to database server: " .
                       mysql_error() );
    
	mysql_select_db( $databaseName )
        or cd_fatalError( "Could not select $databaseName database: " .
                       mysql_error() );
    }


 
/**
 * Closes the database connection.
 */
function cd_closeDatabase() {
    mysql_close();
    }



/**
 * Queries the database, and dies with an error message on failure.
 *
 * @param $inQueryString the SQL query string.
 *
 * @return a result handle that can be passed to other mysql functions.
 */
function cd_queryDatabase( $inQueryString ) {

    $result = mysql_query( $inQueryString )
        or cd_fatalError( "Database query failed:<BR>$inQueryString<BR><BR>" .
                       mysql_error() );

    return $result;
    }



/**
 * Checks whether a table exists in the currently-connected database.
 *
 * @param $inTableName the name of the table to look for.
 *
 * @return 1 if the table exists, or 0 if not.
 */
function cd_doesTableExist( $inTableName ) {
    // check if our table exists
    $tableExists = 0;
    
    $query = "SHOW TABLES";
    $result = cd_queryDatabase( $query );

    $numRows = mysql_numrows( $result );


    for( $i=0; $i<$numRows && ! $tableExists; $i++ ) {

        $tableName = mysql_result( $result, $i, 0 );
        
        if( $tableName == $inTableName ) {
            $tableExists = 1;
            }
        }
    return $tableExists;
    }



function cd_log( $message ) {
    global $enableLog, $tableNamePrefix;

    $slashedMessage = addslashes( $message );
    
    if( $enableLog ) {
        $query = "INSERT INTO $tableNamePrefix"."log VALUES ( " .
            "'$slashedMessage', CURRENT_TIMESTAMP );";
        $result = cd_queryDatabase( $query );
        }
    }



/**
 * Displays the error page and dies.
 *
 * @param $message the error message to display on the error page.
 */
function cd_fatalError( $message ) {
    //global $errorMessage;

    // set the variable that is displayed inside error.php
    //$errorMessage = $message;
    
    //include_once( "error.php" );

    // for now, just print error message
    $logMessage = "Fatal error:  $message";
    
    echo( $logMessage );

    cd_log( $logMessage );
    
    die();
    }



/**
 * Displays the operation error message and dies.
 *
 * @param $message the error message to display.
 */
function cd_operationError( $message ) {
    
    // for now, just print error message
    echo( "ERROR:  $message" );
    die();
    }


/**
 * Recursively applies the addslashes function to arrays of arrays.
 * This effectively forces magic_quote escaping behavior, eliminating
 * a slew of possible database security issues. 
 *
 * @inValue the value or array to addslashes to.
 *
 * @return the value or array with slashes added.
 */
function cd_addslashes_deep( $inValue ) {
    return
        ( is_array( $inValue )
          ? array_map( 'cd_addslashes_deep', $inValue )
          : addslashes( $inValue ) );
    }



/**
 * Recursively applies the stripslashes function to arrays of arrays.
 * This effectively disables magic_quote escaping behavior. 
 *
 * @inValue the value or array to stripslashes from.
 *
 * @return the value or array with slashes removed.
 */
function cd_stripslashes_deep( $inValue ) {
    return
        ( is_array( $inValue )
          ? array_map( 'sb_stripslashes_deep', $inValue )
          : stripslashes( $inValue ) );
    }



function cd_checkPassword( $inFunctionName ) {
    $password = "";
    if( isset( $_REQUEST[ "password" ] ) ) {
        $password = $_REQUEST[ "password" ];
        }

    global $accessPasswords;

    if( ! in_array( $password, $accessPasswords ) ) {
        echo "Incorrect password.";

        cd_log( "Failed $inFunctionName access with password:  $password" );

        die();
        }

    return $password;
    }




// found here:
// http://php.net/manual/en/function.fpassthru.php

function cd_send_file( $path ) {
    session_write_close();
    //ob_end_clean();
    
    if( !is_file( $path ) || connection_status() != 0 ) {
        return( FALSE );
        }
    

    //to prevent long file from getting cut off from     //max_execution_time

    set_time_limit( 0 );

    $name = basename( $path );

    //filenames in IE containing dots will screw up the
    //filename unless we add this

    // sometimes user agent is not set!
    if( ! empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
        
        if( strstr( $_SERVER['HTTP_USER_AGENT'], "MSIE" ) ) {
            $name =
                preg_replace('/\./', '%2e',
                             $name, substr_count($name, '.') - 1);
            }
        }
    
    
    //required, or it might try to send the serving
    //document instead of the file

    header("Cache-Control: ");
    header("Pragma: ");
    header("Content-Type: application/octet-stream");
    header("Content-Length: " .(string)(filesize($path)) );
    header('Content-Disposition: attachment; filename="'.$name.'"');
    header("Content-Transfer-Encoding: binary\n");

    if( $file = fopen( $path, 'rb' ) ) {
        while( ( !feof( $file ) )
               && ( connection_status() == 0 ) ) {
            print( fread( $file, 1024*8 ) );
            flush();
            }
        fclose($file);
        }
    return( (connection_status() == 0 ) and !connection_aborted() );
    }


?>
