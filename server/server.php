<?php



global $cd_version;
$cd_version = "1";

global $cd_minClientVersion;
$cd_minClientVersion = "1";


// edit settings.php to change server' settings
include( "settings.php" );



// no end-user settings below this point


// for use in readable base-32 encoding
// elimates 0/O and 1/I
global $readableBase32DigitArray;
$readableBase32DigitArray =
    array( "2", "3", "4", "5", "6", "7", "8", "9",
           "A", "B", "C", "D", "E", "F", "G", "H", "J", "K", "L", "M",
           "N", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z" );


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
if( get_magic_quotes_gpc() ) {
    // force magic quotes to be added
    $_GET     = array_map( 'cd_stripslashes_deep', $_GET );
    $_POST    = array_map( 'cd_stripslashes_deep', $_POST );
    $_REQUEST = array_map( 'cd_stripslashes_deep', $_REQUEST );
    $_COOKIE  = array_map( 'cd_stripslashes_deep', $_COOKIE );
    }
    



// testing:
//echo "fsfDENasdfIED"; die();
//sleep( rand( 1, 5 ) );


// all calls need to connect to DB, so do it once here
cd_connectToDatabase();

// close connection down below (before function declarations)


// testing:
//sleep( 5 );


// general processing whenver server.php is accessed directly




// grab POST/GET variables
$action = cd_requestFilter( "action", "/[A-Z_]+/i" );


if( $action != "" && $action != "cd_setup" ) {
    // don't check for flush if we may be executing initial database setup
    // (flush breaks in that case)
    cd_checkForFlush();
    }


/*
// debugging:  log full request vars
$fullRequest = "";
foreach( $_REQUEST as $key => $value ) {
    $fullRequest = $fullRequest . ", " . $key . "=>". $value;
    }
cd_log( "Web request with vars $fullRequest" );
*/

$debug = cd_requestFilter( "debug", "/[01]/" );

$remoteIP = "";
if( isset( $_SERVER[ "REMOTE_ADDR" ] ) ) {
    $remoteIP = $_SERVER[ "REMOTE_ADDR" ];
    }


// for debugging of bad server responses
if( false ) {
    echo "BLAHH";
    cd_closeDatabase();
    die();
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
else if( $action == "check_hmac" ) {
    cd_checkHmac();
    }
else if( $action == "start_edit_house" ) {
    cd_startEditHouse();
    }
else if( $action == "start_self_test" ) {
    cd_startSelfTest();
    }
else if( $action == "end_self_test" ) {
    cd_endSelfTest();
    }
else if( $action == "end_edit_house" ) {
    cd_endEditHouse();
    }
else if( $action == "ping_house" ) {
    cd_pingHouse();
    }
else if( $action == "list_houses" ) {
    cd_listHouses();
    }
else if( $action == "start_rob_house" ) {
    cd_startRobHouse();
    }
else if( $action == "end_rob_house" ) {
    cd_endRobHouse();
    }
else if( $action == "list_logged_robberies" ) {
    cd_listLoggedRobberies();
    }
else if( $action == "get_robbery_log" ) {
    cd_getRobberyLog();
    }
else if( $action == "get_self_test_log" ) {
    cd_getSelfTestLog();
    }
else if( $action == "list_auctions" ) {
    cd_listAuctions();
    }
else if( $action == "buy_auction" ) {
    cd_buyAuction();
    }
else if( $action == "check_space_used" ) {
    $space = cd_getTotalSpace();
    echo $space;
    }
else if( $action == "count_users" ) {
    $userCount = cd_countUsers();
    echo $userCount;
    }
else if( $action == "show_data" ) {
    cd_showData();
    }
else if( $action == "show_prices" ) {
    cd_showPrices();
    }
else if( $action == "show_detail" ) {
    cd_showDetail();
    }
else if( $action == "block_user_id" ) {
    cd_blockUserID();
    }
else if( $action == "update_user" ) {
    cd_updateUser();
    }
else if( $action == "update_prices" ) {
    cd_updatePrices();
    }
else if( $action == "default_prices" ) {
    cd_defaultPrices();
    }
else if( $action == "delete_price" ) {
    cd_deletePrice();
    }
else if( $action == "logout" ) {
    cd_logout();
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
else if( $action != "" ) {
    cd_log( "Unknown action request:  $action" );
    echo "DENIED";
    }
else if( preg_match( "/server\.php/", $_SERVER[ "SCRIPT_NAME" ] ) ) {
    // server.php has been called without an action parameter

    // the preg_match ensures that server.php was called directly and
    // not just included by another script
    
    // quick (and incomplete) test to see if we should show instructions
    global $tableNamePrefix;
    
    // check if our tables exists
    $allExist =
        cd_doesTableExist( $tableNamePrefix."server_globals" ) &&
        cd_doesTableExist( $tableNamePrefix."log" ) &&
        cd_doesTableExist( $tableNamePrefix."users" ) &&
        cd_doesTableExist( $tableNamePrefix."houses" ) &&
        cd_doesTableExist( $tableNamePrefix."houses_owner_died" ) &&
        cd_doesTableExist( $tableNamePrefix."robbery_logs" ) &&
        cd_doesTableExist( $tableNamePrefix."scouting_counts" ) &&
        cd_doesTableExist( $tableNamePrefix."prices" ) &&
        cd_doesTableExist( $tableNamePrefix."auction" ) &&
        cd_doesTableExist( $tableNamePrefix."last_names" ) &&
        cd_doesTableExist( $tableNamePrefix."first_names" ) &&
        cd_doesTableExist( $tableNamePrefix."wife_names" ) &&
        cd_doesTableExist( $tableNamePrefix."son_names" ) &&
        cd_doesTableExist( $tableNamePrefix."daughter_names" );
    
        
    if( $allExist  ) {
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



// populate name table from file that is in census percentage format
function cd_populateNameTable( $inFileName, $inTableName ) {
    $totalPopulation = 257746103;
    

    global $tableNamePrefix;

    $tableName = $tableNamePrefix . $inTableName;

    
    if( $file = fopen( $inFileName, "r" ) ) {
        $firstLine = true;

        $query = "INSERT INTO $tableName VALUES ";
        /*
			(1, 'Komal',10),
			(2, 'Ajay',10),
			(3, 'Santosh',10),
			(4, 'Rakesh',10),
			(5, 'Bhau',10);
        */

        // Compute our own cumulative percent from the
        // per-name percent field in the file.
        // Don't use cumulative percent field in file, because
        // we are manually removing some lines from the file,
        // so the cumulative percent field can be off.
        $cumulativePercent = 0;
        
        while( !feof( $file ) ) {
            $line = fgets( $file);

            $tokens = preg_split( "/\s+/", trim( $line ) );
            
            if( count( $tokens ) == 4 ) {
                
                $name = $tokens[0];
                $cumulativePercent += $tokens[1];
                
                $cumulative_count =
                    ( $cumulativePercent / 100 ) * $totalPopulation;
                
                if( ! $firstLine ) {
                    $query = $query . ",";
                    }
                
                $query = $query . " ( $cumulative_count, '$name' )";
                
                $firstLine = false;
                }
            }
        
        fclose( $file );

        $query = $query . ";";

        $result = cd_queryDatabase( $query );
        }
    }



// populate name table from file that is in SSA occurrence-count format
function cd_populateNameTableSSA( $inFileName, $inTableName ) {
    global $tableNamePrefix;

    $tableName = $tableNamePrefix . $inTableName;

    
    if( $file = fopen( $inFileName, "r" ) ) {
        $firstLine = true;

        $query = "INSERT INTO $tableName VALUES ";

        $cumulative_count = 0;
        
        while( !feof( $file ) ) {
            $line = fgets( $file);

            $tokens = preg_split( "/,/", trim( $line ) );
            
            if( count( $tokens ) == 3 ) {
                
                $name = $tokens[0];
                $nameCount = $tokens[2];
                
                $cumulative_count += $nameCount;

                if( ! $firstLine ) {
                    $query = $query . ",";
                    }
                
                $query = $query . " ( $cumulative_count, '$name' )";
                
                $firstLine = false;
                }
            }
        
        fclose( $file );

        $query = $query . ";";

        $result = cd_queryDatabase( $query );
        }
    }



// picks name according to cumulative distribution
function cd_pickName( $inTableName ) {
    global $tableNamePrefix;

    $tableName = $tableNamePrefix . $inTableName;

    $query = "SELECT FLOOR( RAND() * MAX( cumulative_count) ) FROM ".
        $tableName .";";

    $result = cd_queryDatabase( $query );

    $cumulativeTarget = mysql_result( $result, 0, 0 );

    $query = "SELECT MIN( cumulative_count ) FROM ". $tableName .
        " WHERE cumulative_count > $cumulativeTarget;";

    $result = cd_queryDatabase( $query );

    $cumulativePick = mysql_result( $result, 0, 0 );

    $query = "SELECT name FROM ". $tableName .
        " WHERE cumulative_count = $cumulativePick;";
    
    $result = cd_queryDatabase( $query );

    return ucfirst( strtolower(
                        mysql_result( $result, 0, 0 ) ) );
    }



function cd_pickFullName() {
    $firstName = cd_pickName( "first_names" );

    $middleName = cd_pickName( "first_names" );

    while( $middleName == $firstName ) {
        $middleName = cd_pickName( "first_names" );
        }
    
    $character_name =
        $firstName . "_" .
        $middleName . "_" .
        cd_pickName( "last_names" );

    return $character_name;
    }



function cd_restoreDefaultPrices() {
    // default prices

    
    global $tableNamePrefix, $defaultPrices;
    
    $tableName = $tableNamePrefix . "prices";

    // clear old
    $query = "DELETE FROM $tableName";
    cd_queryDatabase( $query );


    $order_number = 0;
    
    foreach( $defaultPrices as $tuple ) {
        $object_id = $tuple[0];
        $price = $tuple[1];
        $in_gallery = $tuple[2];
        $note = mysql_real_escape_string( $tuple[3] );
        
        $query = "INSERT INTO $tableName ".
            "VALUES ( '$object_id', '$price', '$in_gallery', ".
            "         '$order_number','$note' )";

        cd_queryDatabase( $query );

        if( $in_gallery ) {

            // start an auction for any gallery item for which no
            // auction is running AND for which no owner exists
            
            $query = "SELECT COUNT(*) FROM $tableNamePrefix"."auction ".
                "WHERE object_id = '$object_id';";
            
            $result = cd_queryDatabase( $query );
            
            $count = mysql_result( $result, 0, 0 );

            if( $count == 0 ) {

                $query = "SELECT gallery_contents, carried_gallery_contents ".
                    "FROM $tableNamePrefix"."houses ".
                    "WHERE gallery_contents != '#' ".
                    "OR carried_gallery_contents != '#';";

                $result = cd_queryDatabase( $query );
                
                $foundOwned = false;

                $numRows = mysql_numrows( $result );

                for( $i=0; $i<$numRows; $i++ ) {
                    $gallery_contents =
                        mysql_result( $result, $i, "gallery_contents" );

                    $carried_gallery_contents =
                        mysql_result( $result, $i,
                                      "carried_gallery_contents" );

                    if( $gallery_contents != "#" ) {
                        $array = preg_split( "/#/", $gallery_contents );
                        if( in_array( $object_id, $array ) ) {
                            $foundOwned = true;
                            }
                        }
                    if( $carried_gallery_contents != "#" ) {
                        $array =
                            preg_split( "/#/", $carried_gallery_contents );

                        if( in_array( $object_id, $array ) ) {
                            $foundOwned = true;
                            }
                        }
                    
                    }
                
                if( !$foundOwned ) {
                    cd_startAuction( $object_id, $order_number, $price );
                    }
                }
            
            }
        
        $order_number ++;
        }
    
    }


function cd_startAuction( $object_id, $order_number, $start_price ) {
    global $tableNamePrefix, $auctionPriceDropInterval;

    $tableName = $tableNamePrefix . "auction";
    
    $dropInt = $auctionPriceDropInterval;
        
    // DATE_SUB trick to round time down to nearest 3-minute interval
    // found here:
    // http://stackoverflow.com/questions/9680144/
    //        mysql-date-time-round-to-nearest-hour
    $query = "INSERT INTO $tableName ".
        "VALUES ( '$object_id', '$order_number', '$start_price', " .
        "DATE_SUB( ".
        "  DATE_SUB( CURRENT_TIMESTAMP, ".
        "            INTERVAL MOD( MINUTE(CURRENT_TIMESTAMP), ".
        "                          $dropInt ) MINUTE ), ".
        "  INTERVAL SECOND(CURRENT_TIMESTAMP) SECOND ) )";
    
    cd_queryDatabase( $query );
    }



function cd_startInitialAuctions() {
    
    global $tableNamePrefix;    

    // clear any old
    cd_queryDatabase( "DELETE FROM $tableNamePrefix"."auction" );
    
    
    $query = "SELECT object_id, order_number, price FROM ".
        "$tableNamePrefix"."prices WHERE in_gallery = 1;";
    
    $result = cd_queryDatabase( $query );

    $numRows = mysql_numrows( $result );

    for( $i=0; $i<$numRows; $i++ ) {
        $object_id = mysql_result( $result, $i, "object_id" );
        $order_number = mysql_result( $result, $i, "order_number" );
        $price = mysql_result( $result, $i, "price" );
        
        cd_startAuction( $object_id, $order_number, $price );
        }    
    }








/**
 * Creates the database tables needed by seedBlogs.
 */
function cd_setupDatabase() {
    global $tableNamePrefix;


    $tableName = $tableNamePrefix . "server_globals";
    if( ! cd_doesTableExist( $tableName ) ) {

        // this table contains general info about the server
        // use INNODB engine so table can be locked
        $query =
            "CREATE TABLE $tableName(" .
            "last_flush_time DATETIME NOT NULL ) ENGINE = INNODB;";

        $result = cd_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";

        // create one row
        $query = "INSERT INTO $tableName VALUES ( CURRENT_TIMESTAMP );";
        $result = cd_queryDatabase( $query );
        }
    else {
        echo "<B>$tableName</B> table already exists<BR>";
        }


    
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
            "user_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT," .
            "ticket_id VARCHAR(255) NOT NULL," .
            "email VARCHAR(255) NOT NULL," .
            "character_name_history LONGTEXT NOT NULL,".
            "admin TINYINT NOT NULL,".
            "sequence_number INT NOT NULL," .
            "last_price_list_number INT NOT NULL," .
            "blocked TINYINT NOT NULL ) ENGINE = INNODB;";

        $result = cd_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";
        }
    else {
        echo "<B>$tableName</B> table already exists<BR>";
        }

    
    
    $tableName = $tableNamePrefix . "houses";
    // make shadow table for storing dead houses that are still being
    // robbed one last time
    $shadowTableName = $tableNamePrefix . "houses_owner_died";

    if( ! cd_doesTableExist( $tableName ) ) {

        // this table contains general info about each user's house
        // EVERY user has EXACTLY ONE house
        $query =
            "CREATE TABLE $tableName(" .
            "user_id INT NOT NULL PRIMARY KEY," .
            "character_name VARCHAR(62) NOT NULL," .
            "UNIQUE KEY( character_name )," .
            "wife_name VARCHAR(20) NOT NULL," .
            "son_name VARCHAR(20) NOT NULL," .
            "daughter_name VARCHAR(20) NOT NULL," .
            "house_map LONGTEXT NOT NULL," .
            "vault_contents LONGTEXT NOT NULL," .
            "backpack_contents LONGTEXT NOT NULL," .
            "gallery_contents LONGTEXT NOT NULL," .
            "music_seed INT NOT NULL," .
            // times edited since last successful robbery
            "edit_count INT NOT NULL," .
            "self_test_move_list LONGTEXT NOT NULL," .
            "loot_value INT NOT NULL," .
            // loot plus resale value of vault items, rounded
            "value_estimate INT NOT NULL," .
            "wife_present TINYINT NOT NULL," . 
            // loot carried back from latest robbery, not deposited in vault
            // yet.  Deposited when house is next checked out for editing. 
            "carried_loot_value INT NOT NULL," .
            "carried_vault_contents LONGTEXT NOT NULL," .
            "carried_gallery_contents LONGTEXT NOT NULL," .
            "edit_checkout TINYINT NOT NULL,".
            "self_test_running TINYINT NOT NULL,".
            "rob_checkout TINYINT NOT NULL,".
            // ignored if not checked out for robbery
            "robbing_user_id INT NOT NULL," .
            "rob_attempts INT NOT NULL,".
            "robber_deaths INT NOT NULL,".
            "last_ping_time DATETIME NOT NULL,".
            "blocked TINYINT NOT NULL ) ENGINE = INNODB;";

        $result = cd_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";

        // table might not match structure of new houses table,
        // delete it so it will be created below
        if( cd_doesTableExist( $shadowTableName ) ) {
            cd_queryDatabase( "DROP TABLE $shadowTableName;" );
            }
        }
    else {
        echo "<B>$tableName</B> table already exists<BR>";
        }


    
    if( ! cd_doesTableExist( $shadowTableName ) ) {
        $query = "CREATE TABLE $shadowTableName LIKE $tableName;";

        $result = cd_queryDatabase( $query );

        // change properties to allow more than one house in here per user_id
        // since a user can die multiple times in a row, potentially leaving
        // a trail of still-being-robbed-by-someone-else houses in their wake
        cd_queryDatabase( "ALTER TABLE $shadowTableName DROP PRIMARY KEY;" );

        // unique key is now robbing_user_id
        // (and EVERY house in here has rob_checkout set)
        cd_queryDatabase( "ALTER TABLE $shadowTableName ".
                          "ADD PRIMARY KEY( robbing_user_id );" );
        
        // and owner's character name not necessarily unique anymore
        cd_queryDatabase( "ALTER TABLE $shadowTableName ".
                          "DROP INDEX character_name;" );
        
        
        echo "<B>$shadowTableName</B> table created to shadow $tableName<BR>";
        }
    else {
        echo "<B>$shadowTableName</B> table already exists<BR>";
        }

    

    $tableName = $tableNamePrefix . "scouting_counts";
    if( ! cd_doesTableExist( $tableName ) ) {

        // how many time has a give user scouted a given house?
        // may be useful for catching cheaters
        $query =
            "CREATE TABLE $tableName(" .
            "robbing_user_id INT NOT NULL," .
            "house_user_id INT NOT NULL," .
            "count INT NOT NULL," .
            "last_scout_time DATETIME NOT NULL,".
            "PRIMARY KEY( robbing_user_id, house_user_id ) ) ENGINE = INNODB;";

        $result = cd_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";
        }
    else {
        echo "<B>$tableName</B> table already exists<BR>";
        }

    

    


    
    $tableName = $tableNamePrefix . "robbery_logs";
    if( ! cd_doesTableExist( $tableName ) ) {

        // contains move log for each robbery

        $query =
            "CREATE TABLE $tableName(" .
            "log_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT," .
            "user_id INT NOT NULL," .
            "house_user_id INT NOT NULL," .
            "loot_value INT NOT NULL," .
            "wife_money INT NOT NULL," .
            "value_estimate INT NOT NULL," .
            "vault_contents LONGTEXT NOT NULL," .
            "gallery_contents LONGTEXT NOT NULL," .
            "music_seed INT NOT NULL," .
            "rob_attempts INT NOT NULL,".
            "robber_deaths INT NOT NULL,".
            "robber_name VARCHAR(62) NOT NULL," .
            "victim_name VARCHAR(62) NOT NULL," .
            "wife_name VARCHAR(20) NOT NULL," .
            "son_name VARCHAR(20) NOT NULL," .
            "daughter_name VARCHAR(20) NOT NULL," .
            // flag logs for which the owner is now dead (moved onto a new
            // character/life) and can no longer see the log
            // These area candidates for deletion after enough time has passed
            // (admin should still have access to them for a while).
            "owner_now_dead TINYINT NOT NULL," .
            "rob_time DATETIME NOT NULL,".
            "scouting_count INT NOT NULL,".
            "last_scout_time DATETIME NOT NULL,".
            "house_start_map LONGTEXT NOT NULL," .
            "loadout LONGTEXT NOT NULL," .
            "move_list LONGTEXT NOT NULL," .
            "house_end_map LONGTEXT NOT NULL ) ENGINE = INNODB;";

        $result = cd_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";
        }
    else {
        echo "<B>$tableName</B> table already exists<BR>";
        }    



    $tableName = $tableNamePrefix . "prices";
    $pricesCreated = false;
    if( ! cd_doesTableExist( $tableName ) ) {

        $query =
            "CREATE TABLE $tableName(" .
            "object_id INT NOT NULL PRIMARY KEY," .
            "price INT NOT NULL, ".
            "in_gallery TINYINT NOT NULL, ".
            "order_number INT NOT NULL, ".
            "note LONGTEXT NOT NULL ) ENGINE = INNODB;";

        $result = cd_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";
        $pricesCreated = true;
        }
    else {
        echo "<B>$tableName</B> table already exists<BR>";
        }


    
    $tableName = $tableNamePrefix . "auction";
    $auctionCreated = false;
    if( ! cd_doesTableExist( $tableName ) ) {

        $query =
            "CREATE TABLE $tableName(" .
            "object_id INT NOT NULL PRIMARY KEY," .
            "order_number INT NOT NULL, ".
            "start_price INT NOT NULL, ".
            "start_time DATETIME NOT NULL ) ENGINE = INNODB;";

        $result = cd_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";
        $auctionCreated = true;
        }
    else {
        echo "<B>$tableName</B> table already exists<BR>";
        }


    // wait until both tables exist before doing either of these
    if( $pricesCreated ) {
        cd_restoreDefaultPrices();
        }
    if( $auctionCreated ) {
        cd_startInitialAuctions();
        }
    
    
    

    $tableName = $tableNamePrefix . "last_names";
    if( ! cd_doesTableExist( $tableName ) ) {

        // a source list of character last names
        // cumulative count is number of people in 1993 population
        // who have this name or a more common name
        // less common names have higher cumulative counts
        $query =
            "CREATE TABLE $tableName(" .
            "cumulative_count INT NOT NULL PRIMARY KEY," .
            "name VARCHAR(20) NOT NULL );";

        $result = cd_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";

        cd_populateNameTable( "namesLast.txt", "last_names" );
        }
    else {
        echo "<B>$tableName</B> table already exists<BR>";
        }


    $tableName = $tableNamePrefix . "first_names";
    if( ! cd_doesTableExist( $tableName ) ) {


        // a source list of character first names
        // cumulative count is number of people in 1993 population
        // who have this name or a more common name
        // less common names have higher cumulative counts
        $query =
            "CREATE TABLE $tableName(" .
            "cumulative_count INT NOT NULL PRIMARY KEY," .
            "name VARCHAR(20) NOT NULL );";

        $result = cd_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";

        cd_populateNameTable( "namesFirst.txt", "first_names" );
        }
    else {
        echo "<B>$tableName</B> table already exists<BR>";
        }



    
    $tableName = $tableNamePrefix . "wife_names";
    if( ! cd_doesTableExist( $tableName ) ) {


        // a source list of character first names
        // cumulative count is number of people in 1993 population
        // who have this name or a more common name
        // less common names have higher cumulative counts
        $query =
            "CREATE TABLE $tableName(" .
            "cumulative_count INT NOT NULL PRIMARY KEY," .
            "name VARCHAR(20) NOT NULL );";

        $result = cd_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";

        cd_populateNameTableSSA( "namesWife.txt", "wife_names" );
        }
    else {
        echo "<B>$tableName</B> table already exists<BR>";
        }


    
    $tableName = $tableNamePrefix . "son_names";
    if( ! cd_doesTableExist( $tableName ) ) {


        // a source list of character first names
        // cumulative count is number of people in 1993 population
        // who have this name or a more common name
        // less common names have higher cumulative counts
        $query =
            "CREATE TABLE $tableName(" .
            "cumulative_count INT NOT NULL PRIMARY KEY," .
            "name VARCHAR(20) NOT NULL );";

        $result = cd_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";

        cd_populateNameTableSSA( "namesSon.txt", "son_names" );
        }
    else {
        echo "<B>$tableName</B> table already exists<BR>";
        }



    $tableName = $tableNamePrefix . "daughter_names";
    if( ! cd_doesTableExist( $tableName ) ) {


        // a source list of character first names
        // cumulative count is number of people in 1993 population
        // who have this name or a more common name
        // less common names have higher cumulative counts
        $query =
            "CREATE TABLE $tableName(" .
            "cumulative_count INT NOT NULL PRIMARY KEY," .
            "name VARCHAR(20) NOT NULL );";

        $result = cd_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";

        cd_populateNameTableSSA( "namesDaughter.txt", "daughter_names" );
        }
    else {
        echo "<B>$tableName</B> table already exists<BR>";
        }


    }



function cd_showLog() {
    cd_checkPassword( "show_log" );

     echo "[<a href=\"server.php?action=show_data" .
         "\">Main</a>]<br><br><br>";
    
    global $tableNamePrefix;

    $query = "SELECT entry, entry_time FROM $tableNamePrefix"."log ".
        "ORDER BY entry_time DESC;";
    $result = cd_queryDatabase( $query );

    $numRows = mysql_numrows( $result );



    echo "<a href=\"server.php?action=clear_log\">".
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
    cd_checkPassword( "clear_log" );

     echo "[<a href=\"server.php?action=show_data" .
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






// check if we should flush stale checkouts from the database
// do this once every 2 minutes
function cd_checkForFlush() {
    global $tableNamePrefix;

    $tableName = "$tableNamePrefix"."server_globals";
    
    if( !cd_doesTableExist( $tableName ) ) {
        return;
        }

    $flushInterval = "0 0:02:0.000";
    $staleTimeout = "0 0:05:0.000";
    $staleLogTimeout = "5 0:00:0.000";
    // for testing:
    //$flushInterval = "0 0:00:30.000";
    //$staleTimeout = "0 0:01:0.000";
    
    
    cd_queryDatabase( "SET AUTOCOMMIT = 0;" );

    
    $query = "SELECT last_flush_time FROM $tableName ".
        "WHERE last_flush_time < ".
        "SUBTIME( CURRENT_TIMESTAMP, '$flushInterval' ) ".
        "FOR UPDATE;";

    $result = cd_queryDatabase( $query );

    if( mysql_numrows( $result ) > 0 ) {

        // last flush time is old

        global $tableNamePrefix;


        // for each robber who quit game mid-robbery, clear robbery checkout
        $query = "SELECT robbing_user_id FROM $tableNamePrefix"."houses ".
            "WHERE rob_checkout = 1 ".
            "AND last_ping_time < ".
            "SUBTIME( CURRENT_TIMESTAMP, '$staleTimeout' ) FOR UPDATE;";

        $result = cd_queryDatabase( $query );

        $numRows = mysql_numrows( $result );
    
        for( $i=0; $i<$numRows; $i++ ) {

            $robbing_user_id = mysql_result( $result, $i, "robbing_user_id" );

            cd_processStaleCheckouts( $robbing_user_id );
            }

        $totalFlushCount = $numRows;


        // repeat for owner-died shadow table
        // for each robber who quit game mid-robbery, clear robbery checkout
        $query = "SELECT robbing_user_id FROM ".
            "$tableNamePrefix"."houses_owner_died ".
            "WHERE rob_checkout = 1 ".
            "AND last_ping_time < ".
            "SUBTIME( CURRENT_TIMESTAMP, '$staleTimeout' ) FOR UPDATE;";

        $result = cd_queryDatabase( $query );

        $numRows = mysql_numrows( $result );
    
        for( $i=0; $i<$numRows; $i++ ) {

            $robbing_user_id = mysql_result( $result, $i, "robbing_user_id" );

            cd_processStaleCheckouts( $robbing_user_id );
            }

        $totalFlushCount += $numRows;



        
        // for each owner who quit game mid-self-test, clear checkout
        // this will force-kill the owner
        // (Do this now to return paintings to auction house, otherwise
        //  an owner that quits suring self test and never comes back could
        //  trap the paintings forever even though they are logically dead)
        $query = "SELECT user_id FROM $tableNamePrefix"."houses ".
            "WHERE edit_checkout = 1 AND self_test_running = 1 ".
            "AND last_ping_time < ".
            "SUBTIME( CURRENT_TIMESTAMP, '$staleTimeout' ) FOR UPDATE;";

        $result = cd_queryDatabase( $query );

        $numRows = mysql_numrows( $result );
    
        for( $i=0; $i<$numRows; $i++ ) {
            $user_id = mysql_result( $result, $i, "user_id" );

            cd_processStaleCheckouts( $user_id );
            }

        $totalFlushCount += $numRows;

        
        
        // now clear checkout status on stale edit checkouts that were
        // not in the middle of self-test
        $query = "UPDATE $tableNamePrefix"."houses ".
            "SET rob_checkout = 0, edit_checkout = 0 ".
            "WHERE edit_checkout = 1 AND self_test_running = 0 ".
            "AND last_ping_time < ".
            "SUBTIME( CURRENT_TIMESTAMP, '$staleTimeout' );";

        $result = cd_queryDatabase( $query );

        $totalFlushCount += mysql_affected_rows();

        cd_log( "Flush operation checked back in $totalFlushCount ".
                "stale houses." );

        global $enableLog;
        
        if( $enableLog ) {
            // count remaining games for log
            $query = "SELECT COUNT(*) FROM $tableNamePrefix"."houses ".
                "WHERE rob_checkout = 1 OR edit_checkout = 1;";

            $result = cd_queryDatabase( $query );

            $count = mysql_result( $result, 0, 0 );

            cd_log( "After flush, $count houses still checked out." );
            }


        // check for stale robbery logs
        $query = "DELETE ".
            "FROM $tableNamePrefix"."robbery_logs ".
            "WHERE owner_now_dead = 1 ".
            "AND rob_time < ".
            "SUBTIME( CURRENT_TIMESTAMP, '$staleLogTimeout' );";

        $result = cd_queryDatabase( $query );

        $staleLogsRemoved = mysql_affected_rows();

        cd_log( "Flush removed $staleLogsRemoved stale robbery logs." );

        if( $enableLog ) {
            // count remaining games for log
            $query = "SELECT COUNT(*) FROM $tableNamePrefix"."robbery_logs;";

            $result = cd_queryDatabase( $query );

            $count = mysql_result( $result, 0, 0 );

            cd_log( "After flush, $count robbery logs remain." );
            }


        
        // set new flush time

        $query = "UPDATE $tableName SET " .
            "last_flush_time = CURRENT_TIMESTAMP;";
    
        $result = cd_queryDatabase( $query );

    
        }

    cd_queryDatabase( "COMMIT;" );

    cd_queryDatabase( "SET AUTOCOMMIT = 1;" );
    }








function cd_checkUser() {
    global $tableNamePrefix, $ticketServerURL, $sharedEncryptionSecret;

    $email = cd_requestFilter( "email", "/[A-Z0-9._%+-]+@[A-Z0-9.-]+/i" );

    // first, see if user already exists in local users table

    $query = "SELECT ticket_id, blocked ".
        "FROM $tableNamePrefix"."users ".
        "WHERE email = '$email';";

    $result = cd_queryDatabase( $query );
    
    $numRows = mysql_numrows( $result );

    $ticket_id;
    $blocked;
    
    if( $numRows > 0 ) {

        $row = mysql_fetch_array( $result, MYSQL_ASSOC );
    
        $ticket_id = $row[ "ticket_id" ];
        $blocked = $row[ "blocked" ];

        if( $blocked ) {
            echo "DENIED";

            cd_log( "checkUser for $email DENIED, blocked locally" );
            return;
            }
        }
    else {
        // check on ticket server

        $result = file_get_contents(
            "$ticketServerURL".
            "?action=get_ticket_id".
            "&email=$email" );

        // Run a regexp filter to remove non-base-32 characters.
        $match = preg_match( "/[A-HJ-NP-Z2-9]+/", $result, $matches );
        
        if( $result == "DENIED" || $match != 1 ) {
            echo "DENIED";

            cd_log( "checkUser for $email DENIED, email not found ".
                    "or blocked on ticket server" );
            return;
            }
        else {
            $ticket_id = $matches[0];

            // decrypt it

            $ticket_id_bits = cd_readableBase32DecodeToBitString( $ticket_id );

            $ticketLengthBits = strlen( $ticket_id_bits );


            // generate enough bits by hashing shared secret repeatedly
            $hexToMixBits = "";

            $runningSecret = sha1( $sharedEncryptionSecret );
            while( strlen( $hexToMixBits ) < $ticketLengthBits ) {

                $newBits = cd_hexDecodeToBitString( $runningSecret );

                $hexToMixBits = $hexToMixBits . $newBits;

                $runningSecret = sha1( $runningSecret );
                }

            // trim down to bits that we need
            $hexToMixBits = substr( $hexToMixBits, 0, $ticketLengthBits );

            $mixBits = str_split( $hexToMixBits );
            $ticketBits = str_split( $ticket_id_bits );

            // bitwise xor
            $i = 0;
            foreach( $mixBits as $bit ) {
                if( $bit == "1" ) {
                    if( $ticket_id_bits[$i] == "1" ) {
                
                        $ticketBits[$i] = "0";
                        }
                    else {
                        $ticketBits[$i] = "1";
                        }
                    }
                $i++;
                }

            $ticket_id_bits = implode( $ticketBits );

            $ticket_id =
                cd_readableBase32EncodeFromBitString( $ticket_id_bits );
            }
        }


    
    cd_queryDatabase( "SET AUTOCOMMIT=0" );
    
    
    $query = "SELECT user_id, blocked, sequence_number, admin ".
        "FROM $tableNamePrefix"."users ".
        "WHERE ticket_id = '$ticket_id' FOR UPDATE;";
    $result = cd_queryDatabase( $query );
    
    $numRows = mysql_numrows( $result );

    $user_id;
    $sequence_number;
    $admin;
    
    if( $numRows < 1 ) {
        // new user, in ticket server but not here yet

        // create

        // user_id auto-assigned
        $query = "INSERT INTO $tableNamePrefix"."users ".
            "(ticket_id, email, character_name_history, ".
            " admin, sequence_number, ".
            " last_price_list_number, blocked) ".
            "VALUES(" .
            " '$ticket_id', '$email', '', 0, 0, 0, 0 );";
        $result = cd_queryDatabase( $query );

        $user_id = mysql_insert_id();
        $sequence_number = 0;
        $admin = 0;
        
        cd_queryDatabase( "COMMIT;" );
        cd_queryDatabase( "SET AUTOCOMMIT=1" );
        

        cd_newHouseForUser( $user_id );
        }
    else {
        $row = mysql_fetch_array( $result, MYSQL_ASSOC );
    
        $blocked = $row[ "blocked" ];

        cd_queryDatabase( "COMMIT;" );
        cd_queryDatabase( "SET AUTOCOMMIT=1" );
        
        
        if( $blocked ) {
            echo "DENIED";

            

            cd_log( "checkUser for $email DENIED, blocked on castle server" );

            
            return;
            }
        
        $user_id = $row[ "user_id" ];
        $sequence_number = $row[ "sequence_number" ];
        $admin = $row[ "admin" ];

        
        $query = "SELECT COUNT(*) ".
            "FROM $tableNamePrefix"."houses ".
            "WHERE user_id = '$user_id';";
        $result = cd_queryDatabase( $query );

        $houseCount = mysql_result( $result, 0, 0 );

        if( $houseCount < 1 ) {
            cd_log( "Warning:  user $user_id present, ".
                    "but had no house. Creating one." );
            cd_newHouseForUser( $user_id );
            }
        }

    global $cd_minClientVersion;
    
    echo "$cd_minClientVersion $user_id $sequence_number $admin OK";
    }



function cd_checkHmac() {
    if( ! cd_verifyTransaction() ) {
        return;
        }

    echo "OK";
    }


function cd_processStaleCheckouts( $user_id ) {
    global $tableNamePrefix;


    // first find all stale robberies
    $query = "SELECT COUNT(*) FROM $tableNamePrefix"."houses ".
        "WHERE rob_checkout = 1 AND robbing_user_id = '$user_id';";
    $result = cd_queryDatabase( $query );

    $staleRobberyCount = mysql_result( $result, 0, 0 );

    if( $staleRobberyCount ) {
        // clear all the robberies themselves

        // each abandonned robbery counts as a death that occurred in that
        // house
        $query = "UPDATE $tableNamePrefix"."houses SET ".
            "rob_checkout = 0, robber_deaths = robber_deaths + 1 ".
            "WHERE robbing_user_id = '$user_id';";
        cd_queryDatabase( $query );
        }



    // next find all stale robberies in shadow table (for houses
    // where owner died)
    $query = "SELECT COUNT(*) FROM $tableNamePrefix"."houses_owner_died ".
        "WHERE rob_checkout = 1 AND robbing_user_id = '$user_id';";
    $result = cd_queryDatabase( $query );

    $staleShadowRobberyCount = mysql_result( $result, 0, 0 );

    if( $staleShadowRobberyCount ) {
        // clear all the robberies themselves

        $query = "SELECT user_id, gallery_contents ".
            "FROM $tableNamePrefix"."houses_owner_died ".
            "WHERE robbing_user_id = '$user_id' FOR UPDATE;";

        $result = cd_queryDatabase( $query );
        
        $row = mysql_fetch_array( $result, MYSQL_ASSOC );
        
        $gallery_contents = $row[ "gallery_contents" ];
        $owner_id = $row[ "user_id" ];

        
        // remove this house from the shadow table
        $query = "DELETE FROM $tableNamePrefix"."houses_owner_died WHERE ".
            " robbing_user_id = $user_id;";
        cd_queryDatabase( $query );

        
        // clear scouting counts for every robber, since house gone
        $query = "DELETE FROM $tableNamePrefix"."scouting_counts WHERE ".
            " house_user_id = $owner_id;";
        cd_queryDatabase( $query );

        
        // return any remaining gallery stuff to auction house
        cd_returnGalleryContents( $gallery_contents );
        }


    
    

    // now find stale self tests
    $query = "SELECT COUNT(*) FROM $tableNamePrefix"."houses ".
        "WHERE edit_checkout = 1 AND self_test_running = 1 ".
        "AND user_id = '$user_id';";
    $result = cd_queryDatabase( $query );

    $staleSelfTestCount = mysql_result( $result, 0, 0 );

    
    if( $staleRobberyCount > 0 ||
        $staleSelfTestCount > 0 ||
        $staleShadowRobberyCount > 0 ) {

        // user abandonned game while in danger of dying

        // force kill them

        cd_newHouseForUser( $user_id );
        }
    }



function cd_startEditHouse() {
    global $tableNamePrefix;

    if( ! cd_verifyTransaction() ) {
        return;
        }

    $user_id = cd_getUserID();

    
    cd_queryDatabase( "SET AUTOCOMMIT=0" );

    cd_processStaleCheckouts( $user_id );
    
    
    // automatically ignore blocked users and houses already checked
    // out for robbery
    
    $query = "SELECT wife_name, son_name, daughter_name, ".
        "house_map, vault_contents, backpack_contents, ".
        "gallery_contents, ".
        "loot_value, ".
        "carried_loot_value, carried_vault_contents, ".
        "carried_gallery_contents, ".
        "edit_count, music_seed FROM $tableNamePrefix"."houses ".
        "WHERE user_id = '$user_id' AND blocked='0' ".
        "AND rob_checkout = 0 FOR UPDATE;";

    $result = cd_queryDatabase( $query );

    $numRows = mysql_numrows( $result );
    
    if( $numRows < 1 ) {
        cd_transactionDeny();
        return;
        }
    $row = mysql_fetch_array( $result, MYSQL_ASSOC );

    $wife_name = $row[ "wife_name" ];
    $son_name = $row[ "son_name" ];
    $daughter_name = $row[ "daughter_name" ];
    
    $house_map = $row[ "house_map" ];
    $vault_contents = $row[ "vault_contents" ];
    $backpack_contents = $row[ "backpack_contents" ];
    $gallery_contents = $row[ "gallery_contents" ];
    $loot_value = $row[ "loot_value" ];
    $edit_count = $row[ "edit_count" ];
    $music_seed = $row[ "music_seed" ];

    
    $carried_loot_value = $row[ "carried_loot_value" ];
    $carried_vault_contents = $row[ "carried_vault_contents" ];
    $carried_gallery_contents = $row[ "carried_gallery_contents" ];

    // add carried stuff into vault
    $loot_value += $carried_loot_value;
    $vault_contents =
        cd_idQuantityUnion( $vault_contents, $carried_vault_contents );
    
    if( $gallery_contents == "#" ) {
        $gallery_contents = $carried_gallery_contents;
        }
    else {
        if( $carried_vault_contents != "#" ) {
            // append
            $gallery_contents =
                $gallery_contents . "#" . $carried_gallery_contents;
            }
        }

    $value_estimate = cd_computeValueEstimate( $loot_value, $vault_contents );
    
    $query = "UPDATE $tableNamePrefix"."houses SET ".
        "edit_checkout = 1, last_ping_time = CURRENT_TIMESTAMP, ".
        "loot_value = '$loot_value', value_estimate = '$value_estimate', ".
        "vault_contents = '$vault_contents', ".
        "gallery_contents = '$gallery_contents', ".
        "carried_loot_value = 0, ".
        "carried_vault_contents = '#', ".
        "carried_gallery_contents = '#' ".
        "WHERE user_id = $user_id;";
    cd_queryDatabase( $query );


    
    $query = "SELECT last_price_list_number FROM $tableNamePrefix"."users ".
        " WHERE user_id = '$user_id' FOR UPDATE;";

    $result = cd_queryDatabase( $query );

    $numRows = mysql_numrows( $result );
    
    if( $numRows < 1 ) {
        cd_transactionDeny();
        return;
        }
    $row = mysql_fetch_array( $result, MYSQL_ASSOC );

    $last_price_list_number = $row[ "last_price_list_number" ];


    $last_price_list_number ++;


    $query = "UPDATE $tableNamePrefix"."users SET ".
        "last_price_list_number = '$last_price_list_number' ".
        "WHERE user_id = $user_id;";
    cd_queryDatabase( $query );

    
    
    cd_queryDatabase( "COMMIT;" );
    cd_queryDatabase( "SET AUTOCOMMIT=1" );


    $query = "SELECT object_id, price, in_gallery ".
        "FROM $tableNamePrefix"."prices ORDER BY order_number;";

    $result = cd_queryDatabase( $query );

    $numRows = mysql_numrows( $result );

    $priceListBody = "";

    $firstRow = true;
    
    for( $i=0; $i<$numRows; $i++ ) {
        // leave gallery objects out of price list, because their
        // prices are just start auction prices, and not current
        // (and don't want to waste space in price list)
        $in_gallery = mysql_result( $result, $i, "in_gallery" );

        if( !$in_gallery ) {
            
            if( !$firstRow ) {
                $priceListBody = $priceListBody . "#";
                }
            $firstRow = false;
            
            $object_id = mysql_result( $result, $i, "object_id" );
            $price = mysql_result( $result, $i, "price" );
            
            $priceListBody = $priceListBody . "$object_id"."@"."$price";
            }
        }

    global $serverSecretKey;
    
    $signature =
        cd_hmac_sha1( $serverSecretKey,
                      $last_price_list_number . $priceListBody );


    $must_self_test = 0;

    if( $edit_count == 0 ) {
        $must_self_test = 1;
        }
        
        
    
    echo $house_map;
    echo "\n";
    echo $vault_contents;
    echo "\n";
    echo $backpack_contents;
    echo "\n";
    echo $gallery_contents;
    echo "\n";
    echo $last_price_list_number . ":" . $priceListBody . ":" . $signature;
    echo "\n";
    echo $loot_value;
    echo "\n";
    echo $must_self_test;
    echo "\n";
    echo $music_seed;
    echo "\n";
    echo $wife_name;
    echo "\n";
    echo $son_name;
    echo "\n";
    echo $daughter_name;
    echo "\nOK";
    }




function cd_idQuanityStringToArray( $inIDQuantityString ) {

    $pairArray = preg_split( "/#/", $inIDQuantityString );

    $result = array();
    
    if( $inIDQuantityString == "#" ) {
        return $result;
        }

    foreach( $pairArray as $pair ) {
        $pairParts = preg_split( "/:/", $pair );

        if( count( $pairParts ) == 2 ) {
            $id = $pairParts[0];
            $quantity = $pairParts[1];

            $result[ $id ] = $quantity;
            }
        }
    return $result;
    }



function cd_idQuanityArrayToString( $inArray ) {
    ksort( $inArray );
    
    $pairArray = array();
    
    foreach( $inArray as $id => $quantity ) {
        // append
        $pairArray[] = "$id:$quantity";
        }

    $resultString = implode( "#", $pairArray );

    if( $resultString == "" ) {
        $resultString = "#";
        }

    return $resultString;
    }



// takes strings that are ID:quantity pairs, like:
// 101:3#3:10#5:1#102:1
//
// And computes a union operation, returning a new string
// (where the quanity for each ID in the result is the sum of the
//  quantities of that ID in A and B)
function cd_idQuantityUnion( $inIDQuantityStringA, $inIDQuantityStringB ) {

    $arrayA = cd_idQuanityStringToArray( $inIDQuantityStringA );
    $arrayB = cd_idQuanityStringToArray( $inIDQuantityStringB );
    
    // start with B as base, sum in (or append) ID/quantities from A
    $result = $arrayB;

    foreach( $arrayA as $id => $quantity ) {    
        if( array_key_exists( $id, $result ) ) {
            // exists in B, sum with A's quantity
            $result[ $id ] += $quantity;
            }
        else {
            // doesn't exist in B, insert A's quantity
            $result[ $id ] = $quantity;
            }
        }

    return cd_idQuanityArrayToString( $result );
    }




// takes strings that are ID:quantity pairs, like:
// 101:3#3:10#5:1#102:1
//
// And computes a subtraction operation, returning a new string,
// subtracting the quantities in B from A.
// This may result in negative quanties in the result.
function cd_idQuantitySubtract( $inIDQuantityStringA, $inIDQuantityStringB ) {

    $arrayA = cd_idQuanityStringToArray( $inIDQuantityStringA );
    $arrayB = cd_idQuanityStringToArray( $inIDQuantityStringB );
    
    // start with A as base, subract out (or append negative)
    // ID/quantities from B
    $result = $arrayA;

    foreach( $arrayB as $id => $quantity ) {    
        if( array_key_exists( $id, $result ) ) {
            // exists in A, subtract B's quantity
            $result[ $id ] -= $quantity;
            
            if( $result[ $id ] == 0 ) {
                // remove from array completely if quantity 0
                unset( $result[ $id ] );
                }
            }
        else {
            // doesn't exist in A, insert B's negative quantity
            $result[ $id ] = -$quantity;
            }
        }

    return cd_idQuanityArrayToString( $result );
    }



// computes resale value of items in an ID:quantity list string
function cd_idQuantityToResaleValue( $inIDQuantityString, $inPriceArray ) {
    global $resaleRate;
    
    $quantityArray = cd_idQuanityStringToArray( $inIDQuantityString );

    $totalValue = 0;


    foreach( $quantityArray as $id => $quantity ) {

        $totalValue += floor( $quantity * $inPriceArray[$id] * $resaleRate );
        }
    
    return $totalValue;
    }



// returns an array mapping objectIDs to prices
function cd_getPriceArray() {
    global $tableNamePrefix;
    
    $query = "SELECT object_id, price ".
        "FROM $tableNamePrefix"."prices;";

    $result = cd_queryDatabase( $query );

    $numRows = mysql_numrows( $result );

    $array = array();
    
    for( $i=0; $i<$numRows; $i++ ) {
        
        $object_id = mysql_result( $result, $i, "object_id" );

        $price = mysql_result( $result, $i, "price" );

        $array[ $object_id ] = $price;
        }
    return $array;
    }




function cd_computeValueEstimate( $inLootValue, $inVaultContents ) {
    $value_estimate =
        $inLootValue +
        cd_idQuantityToResaleValue( $inVaultContents, cd_getPriceArray() );

    // avoid nonsense results below
    if( $value_estimate < 10 ) {
        return $value_estimate;
        }
        
    // isolate the highest two digits
    // 538 => 530
    // 25,343 => 25,000
    $tenPower = pow( 10, floor( log( $value_estimate, 10 ) ) - 1 );

    $value_estimate = $tenPower * floor( $value_estimate / $tenPower );

    return $value_estimate;
    }




function cd_endEditHouse() {
    global $tableNamePrefix;

    if( ! cd_verifyTransaction() ) {
        return;
        }

    $user_id = cd_getUserID();

    
    cd_queryDatabase( "SET AUTOCOMMIT=0" );

    // automatically ignore blocked users and houses already checked
    // out for robbery
    
    $query = "SELECT user_id, edit_count, loot_value, house_map, ".
        "vault_contents, backpack_contents, gallery_contents, ".
        "self_test_running ".
        "FROM $tableNamePrefix"."houses ".
        "WHERE user_id = '$user_id' AND blocked='0' ".
        "AND rob_checkout = 0 and edit_checkout = 1 FOR UPDATE;";

    $result = cd_queryDatabase( $query );

    $numRows = mysql_numrows( $result );
    
    if( $numRows < 1 ) {
        cd_transactionDeny();
        return;
        }
    
    $row = mysql_fetch_array( $result, MYSQL_ASSOC );

    $edit_count = $row[ "edit_count" ];
    $self_test_running = $row[ "self_test_running" ];
    $loot_value = $row[ "loot_value" ];
    $old_house_map = $row[ "house_map" ];
    $old_vault_contents = $row[ "vault_contents" ];
    $old_backpack_contents = $row[ "backpack_contents" ];
    $old_gallery_contents = $row[ "gallery_contents" ];
    
    
    $house_map = cd_requestFilter( "house_map", "/[#0-9,:!]+/" );

    $vault_contents = cd_requestFilter( "vault_contents", "/[#0-9:]+/" );

    $backpack_contents = cd_requestFilter( "backpack_contents", "/[#0-9:]+/" );

    $gallery_contents = cd_requestFilter( "gallery_contents", "/[#0-9]+/" );

    $price_list = cd_requestFilter( "price_list",
                                    "/\d+:[0-9@#]+:[A-F0-9]+/i" );

    $edit_list = cd_requestFilter( "edit_list", "/[0-9@#]+/" );

    $family_exit_paths = cd_requestFilter( "family_exit_paths", "/[0-9@#]+/" );
    
    $purchase_list = cd_requestFilter( "purchase_list", "/[#0-9:]+/" );
    $sell_list = cd_requestFilter( "sell_list", "/[#0-9:]+/" );

    // different from move_list in endRobHouse, because tW@X moves (tool use)
    // aren't allowed
    // also, no valid self test ends with L (leaving house)
    $self_test_move_list =
        cd_requestFilter( "self_test_move_list", "/[m0-9#S]+/" );

    
    $died = cd_requestFilter( "died", "/[01]/" );


    if( $died == 1 ) {
        // don't need to check edit, because player died and house
        // will be destroyed anyway

        cd_queryDatabase( "COMMIT;" );
        cd_queryDatabase( "SET AUTOCOMMIT=1" );

        
        cd_newHouseForUser( $user_id );

        echo "OK";

        // skip rest
        return;
        }



    $editArray = preg_split( "/#/", $edit_list );
    
    $numEdits = count( $editArray );


    if( $edit_list == "" ) {
        // split on empty string returns array with 1 element, which screws
        // up loop below
        $numEdits = 0;
        }

    
    $sellArray = preg_split( "/#/", $sell_list );

    $numSold = count( $sellArray );
        
    
    if( $sell_list == "#" ) {
        $numSold = 0;
        }

    
    $purchaseArray = preg_split( "/#/", $purchase_list );

    $numPurchases = count( $purchaseArray );
        
    
    if( $purchase_list == "#" ) {
        $numPurchases = 0;
        }

    
    if( $numEdits == 0 && $numSold == 0 && $numPurchases == 0 &&
        $old_backpack_contents == $backpack_contents &&
        $old_vault_contents == $vault_contents &&
        ! $self_test_running ) {
        // don't need to check edit or update anything,
        // because there wasn't an edit, or a sale, or a purchase.
        // nor was inventory moved around in backpack or vault
        // nor was a forced self-test running (post robbery, which
        // counts as an edit even if the owner changed nothing)

        // (case where user visited house without changing anything)

        // don't update the edit count either

        $query = "UPDATE $tableNamePrefix"."houses ".
            "SET rob_checkout = 0, edit_checkout = 0, self_test_running = 0 ".
            "WHERE user_id = '$user_id';";

        cd_queryDatabase( $query );

        cd_queryDatabase( "COMMIT;" );
        cd_queryDatabase( "SET AUTOCOMMIT=1" );

        echo "OK";

        // skip rest
        return;
        }
    

    if( $numEdits > 0 || $self_test_running ) {
        // don't count purchases or sales or inventory transfer as edits
        $edit_count ++;
        }

    

    $query = "SELECT last_price_list_number ".
        "FROM $tableNamePrefix"."users ".
        "WHERE user_id = '$user_id';";

    $result = cd_queryDatabase( $query );

    $numRows = mysql_numrows( $result );
    
    if( $numRows < 1 ) {
        cd_transactionDeny();
        return;
        }
    $row = mysql_fetch_array( $result, MYSQL_ASSOC );

    $last_price_list_number = $row[ "last_price_list_number" ];


    $priceListParts = preg_split( "/:/", $price_list );


    if( count( $priceListParts ) != 3 ) {
        cd_log( "House check-in with badly-formatted price list denied" );
        cd_transactionDeny();
        return;
        }

    if( $last_price_list_number != $priceListParts[0] ) {
        cd_log( "House check-in with stale price list denied" );
        cd_transactionDeny();
        return;
        }

    $priceListBody = $priceListParts[1];
    $sig = $priceListParts[2];
    
    global $serverSecretKey;
    
    $true_sig =
        cd_hmac_sha1( $serverSecretKey,
                      $last_price_list_number . $priceListBody );

    if( $true_sig != $sig ) {
        cd_log( "House check-in with badly-signed price list denied" );
        cd_transactionDeny();
        return;
        }


    // valid, fresh price list, signed by this server!

    
    $priceList = preg_split( "/#/", $priceListBody );

    // build an array mapping object_id => price
    $priceArray = array();

    $numPrices = count( $priceList );


    for( $i=0; $i<$numPrices; $i++ ) {
        $priceParts = preg_split( "/@/", $priceList[$i] );
        
        $priceArray[ $priceParts[0] ] = $priceParts[1];
        }

    // finally, stick 0 prices for vault placement and floor placement
    // (floor placement is erasing---free)
    $priceArray[ 999 ] = 0;
    $priceArray[ 0 ] = 0;

    
    // also for all possible wives/sons/daughters
    global $wifeList;
    foreach( $wifeList as $wife ) {
        $priceArray[ $wife ] = 0;
        }
    global $sonList;
    foreach( $sonList as $son ) {
        $priceArray[ $son ] = 0;
        }
    global $daughterList;
    foreach( $daughterList as $daughter ) {
        $priceArray[ $daughter ] = 0;
        }
    

    // now we need to check new house map to make sure it is a valid edit

    $houseArray = preg_split( "/#/", $house_map );

    $numHouseCells = count( $houseArray );

    // check 0:
    // house map is 32x32
    if( $numHouseCells != 32 * 32 ) {
        cd_log( "House check-in with $numHouseCells map cells denied" );
        cd_transactionDeny();
        return;
        }
    
    
    // check 1:
    // make sure it has exactly 1 vault and correct number of ext walls
    
    $vaultCount = 0;
    $extWallCount = 0;
    for( $i=0; $i<$numHouseCells; $i++ ) {
        if( $houseArray[$i] == 999 ) {
            $vaultCount++;
            }
        else if( $houseArray[$i] == 998 ) {
            $extWallCount++;
            }
        }

    if( $vaultCount != 1 ||
        $extWallCount !=
        // top row, bottom row
        32 + 32 +
        // edges between top and bottom
        2 * 30
        // empty spot on left edge for start position
        - 1 ) {

        cd_log(
            "House check-in with bad vault count ($vaultCount) ".
            "or ext wall count ($extWallCount) denied" );

        cd_transactionDeny();
        return;
        }


    // check 2:
    // make sure the start location is empty

    // row 16, column 0
    $startIndex = 16 * 32 + 0;

    if( $houseArray[ $startIndex ] != 0 ) {
        cd_log( "House check-in with bad start location on map denied" );
        cd_transactionDeny();
        return;
        }

    
    // check 3:
    // all edges must be exterior walls (or blank for start spot)

    // top row
    for( $i=0; $i<32; $i++ ) {
        if( $houseArray[$i] != 998 ) {
            cd_log( "House check-in with bad ext wall placement denied" );
            cd_transactionDeny();
            return;
            }
        }

    // bottom row
    for( $i=31*32; $i<32*32; $i++ ) {
        if( $houseArray[$i] != 998 ) {
            cd_log( "House check-in with bad ext wall placement denied" );
            cd_transactionDeny();
            return;
            }
        }

    // sides in between top and bottom rows
    for( $y=1; $y<31; $y++ ) {

        $left = $y * 32;

        if( $houseArray[$left] != 998 ) {
            // start location is one exception
            if( $y != 16 ) {
                cd_log( "House check-in with bad ext wall placement denied" );
                cd_transactionDeny();
                return;
                }
            }
        
        
        $right = $y * 32 + 31;
        if( $houseArray[$right] != 998 ) {
            cd_log( "House check-in with bad ext wall placement denied" );
            cd_transactionDeny();
            return;
            }
        }


    // Add revenue from sold items into loot_value
    // Do this before totalling cost of house edits, because
    // player may have used money from loot sold to buy house edits
    global $resaleRate;
    
    
    
    for( $i=0; $i<$numSold; $i++ ) {
        $sellParts = preg_split( "/:/", $sellArray[$i] );

        if( count( $sellParts ) != 2 ) {
            cd_log(
                "House check-in with badly-formatted sell list denied" );
            cd_transactionDeny();
            return;
            }

        $id = $sellParts[0];
        $quantity = $sellParts[1];
        

        if( ! array_key_exists( "$id", $priceArray ) ) {
            // id's not on the price list can't be bought!
            cd_log(
                "House check-in with unbuyable object in sell denied" );
            cd_transactionDeny();
            return;
            }

        // items sold back for half their value, rounded down
        $loot_value += $quantity * floor( $priceArray[ "$id" ] * $resaleRate );
        }

    
    
    
    
    // now walk through edit list, totalling cost, and making sure
    // that changes result in new, submitted house map

    $oldHouseArray = preg_split( "/#/", $old_house_map );

    $editedHouseArray = $oldHouseArray;
    

    if( $numEdits > 0 && $self_test_move_list == "#" ) {

        cd_log( "House check-in failed because edit list not accompanied by ".
                "a self-test move list." );
        cd_transactionDeny();
        return;    
        }
    

    for( $i=0; $i<$numEdits; $i++ ) {

        // object_id@index
        $editParts = preg_split( "/@/", $editArray[$i] );

        if( count( $editParts ) != 2 ) {
            cd_log( "House check-in with badly-formatted edit list denied" );
            cd_transactionDeny();
            return;
            }

        $id = $editParts[0];
        $mapIndex = $editParts[1];
        
        if( $mapIndex >= 32 * 32 || $mapIndex < 0 ) {
            // out of bounds
            cd_log( "House check-in with out-of-bounds edit denied" );
            cd_transactionDeny();
            return;
            }


        if( ! array_key_exists( "$id", $priceArray ) ) {
            // id's not on the price list can't be placed!
            cd_log( "House check-in with unplaceable object in edit denied" );
            cd_transactionDeny();
            return;
            }
        
        
        $editedHouseArray[ $editParts[1] ] = $id;

        $loot_value -= $priceArray[ "$id" ];

        if( $loot_value < 0 ) {
            // more edits than they could afford
            cd_log( "House check-in with exceeded player budget denied" );
            cd_transactionDeny();
            return;
            }
        }

    // all edits applied


    // auto-reset any object states to 0 that aren't stuck
    // these may be left over from previous robbery (switches that are toggled,
    //  etc)
    // (client does this every time an edit completes)

    for( $i=0; $i<$numHouseCells; $i++ ) {

        $cellObjects = preg_split( "/,/", $editedHouseArray[$i] );

        $numObjects = count( $cellObjects );

        for( $j=0; $j<$numObjects; $j++ ) {

            $objectParts = preg_split( "/:/", $cellObjects[$j] );

            if( count( $objectParts ) > 1 ) {

                // second part is state

                if( strstr( $objectParts[1], "!" ) ) {
                    // stuck, don't change state
                    }
                else {
                    // set state back to 0, which means state is simply left
                    // off (implied ":0")
                    // so drop second part of object, including ":" separator
                    
                    $cellObjects[$j] = $objectParts[0];
                    }
                }
            }

        $editedHouseArray[$i] = implode( ",", $cellObjects );        
        }
    

    
    
    $edited_house_map = implode( "#", $editedHouseArray );


    if( $edited_house_map != $house_map ) {
        // edits + old map don't add up to the map that was submitted
        cd_log( "House check-in with map and edits mismatch denied" );
        
        cd_transactionDeny();
        return;
        }
    

    // Well...
    // if we get here, then we have a valid, edited house map.




    
    
    // NEXT:
    // Check that purchases don't exceed loot value,

    
    for( $i=0; $i<$numPurchases; $i++ ) {
        $purchaseParts = preg_split( "/:/", $purchaseArray[$i] );

        if( count( $purchaseParts ) != 2 ) {
            cd_log(
                "House check-in with badly-formatted purchase list denied" );
            cd_transactionDeny();
            return;
            }

        $id = $purchaseParts[0];
        $quantity = $purchaseParts[1];
        

        if( ! array_key_exists( "$id", $priceArray ) ) {
            // id's not on the price list can't be bought!
            cd_log(
                "House check-in with unbuyable object in purchase denied" );
            cd_transactionDeny();
            return;
            }
        
        $loot_value -= $quantity * $priceArray[ "$id" ];

        if( $loot_value < 0 ) {
            // more edits than they could afford
            cd_log( "House check-in with ".
                    "purchases exceeding player budget denied" );
            cd_transactionDeny();
            return;
            }

        }
    

    // Finally, make sure
    // (Vault U Backpack) =  ( (old_Vault U old_Backpack)  + Purchases  - Sold)
    
    $ownedUnion = cd_idQuantityUnion( $vault_contents, $backpack_contents );

    $oldOwnedUnion = cd_idQuantityUnion( $old_vault_contents,
                                         $old_backpack_contents );
    $newOwnedUnion = cd_idQuantityUnion( $oldOwnedUnion, $purchase_list );

    $newOwnedUnion = cd_idQuantitySubtract( $newOwnedUnion, $sell_list );   
    
    
    if( $ownedUnion != $newOwnedUnion ) {
        cd_log( "House check-in with ".
                " purchases/sales that don't match contents of vault and ".
                " backpack denied (actually owned $ownedUnion)".
                " (purchases show we own $newOwnedUnion )" );
        cd_transactionDeny();
        return;
        }

    
    // also, make sure gallery contains no unexpected items

    if( $old_gallery_contents != "#" ) {

        if( $gallery_contents == "#" ) {
            cd_log( "House check-in with ".
                        " unexpected empty gallery denied" );
            cd_transactionDeny();
            return;
            }
        
        $oldGalleryArray = preg_split( "/#/", $old_gallery_contents );
        $newGalleryArray = preg_split( "/#/", $gallery_contents );

        foreach( $newGalleryArray as $item ) {    
            if( ! in_array( $item, $oldGalleryArray ) ) {
                cd_log( "House check-in with ".
                        " extra gallery items denied" );
                cd_transactionDeny();
                return;
                }
            }
        }
    else if( $gallery_contents != "#" ) {
        cd_log( "House check-in with ".
                " extra gallery items denied" );
        cd_transactionDeny();
        return;
        }
    

    $value_estimate = cd_computeValueEstimate( $loot_value, $vault_contents );




    // now check family exit paths

    // first, find all living family locations on map
    $familyObjects = array_merge( $wifeList, $sonList, $daughterList );
    $familyLocations = array();
    $wife_present = 0;
    
    $houseArray = preg_split( "/#/", $house_map );

    $index = 0;
    foreach( $houseArray as $cell ) {
        $cellObjects = preg_split( "/,/", $cell );

        // only consider first object (rest are mobile)
        $objectParts = preg_split( "/:/", $cellObjects[0] );

        $numParts = count( $objectParts );

        $objectAlive = true;

        if( $numParts > 1 && $objectParts[1] != 0 ) {
            $objectAlive = false;
            }
        
        if( array_search( $objectParts[0], $familyObjects ) !== false
            &&
            $objectAlive ) {
            
            $familyLocations[] = $index;

            if( array_search( $objectParts[0], $wifeList ) !== false ) {
                $wife_present = true;
                }
            }
        
        $index ++;
        }

    
    
    
    
    $numPaths = 0;
    // array of arrays
    $paths = array();
    
    if( $family_exit_paths == "" || $family_exit_paths == "##" ) {
        $numPaths = 0;
        }
    else {
        $pathStrings = preg_split( "/##/", $family_exit_paths );
        $numPaths = count( $pathStrings );

        foreach( $pathStrings as $string ) {
            $paths[] = preg_split( "/#/", $string );
            }
        }


    
    global $mobileList;

    // exit paths must make it all the way to map start
    $exitIndex = 32 * 16;

    // check that paths are valid
    // empty (except for mobiles or other family members)
    foreach( $paths as $path ) {
        $numPathSteps = count( $path );
        
        if( $numPathSteps == 0 ) {
            cd_log( "House check-in with ".
                    " 0-length exit path denied" );
            cd_transactionDeny();
            return;
            }

        if( $path[ $numPathSteps - 1 ] != $exitIndex ) {
            cd_log( "House check-in with ".
                    " exit path not reaching exit denied" );
            cd_transactionDeny();
            return;
            }

        // make sure paths contiguous
        $lastX = $path[0] % 32;
        $lastY = (int)( $path[0] / 32 );
        
        foreach( $path as $step ) {

            $thisX = $step % 32;
            $thisY = (int)( $step / 32 );

            $stepX = abs( $thisX - $lastX );
            $stepY = abs( $thisY - $lastY );
            
            
            if( // diagonal 
                ( $stepX != 0 &&
                  $stepY != 0 )
                ||
                // too big
                $stepX > 1 || $stepY > 1 ) {

                cd_log( "House check-in with ".
                        " non-contiguous exit path denied" );
                cd_transactionDeny();
                return;
                }

            $lastX = $thisX;
            $lastY = $thisY;
            
                
            $cell = $houseArray[ $step ];
                    
            $cellObjects = preg_split( "/,/", $cell );

            // only consider first object (rest are mobile)
            $objectParts = preg_split( "/:/", $cellObjects[0] );
                    
            if( // clear
                $objectParts[0] == 0
                ||
                // or family
                array_search( $objectParts[0],
                              $familyObjects ) !== false
                ||
                // or mobile
                array_search( $objectParts[0],
                              $mobileList ) !== false ) {

                // okay!
                }
            else {
                // blocked
                cd_log( "House check-in with ".
                        " blocked family exit path denied" );
                cd_transactionDeny();
                return;
                }
            }
        }
    

    

    
    // check that exit path exits for each one
    foreach( $familyLocations as $location ) {

        if( $numPaths == 0 ) {
            cd_log( "House check-in with ".
                    " family members but no exit paths denied" );
            cd_transactionDeny();
            return;
            }

        $found = false;
        
        foreach( $paths as $path ) {
            
            if( $path[0] == $location ) {
                // a path for this family member!
                $found = true;
                break;
                }            
            }

        if( ! $found ) {
            cd_log( "House check-in with ".
                    " missing exit path for a family member denied" );
            cd_transactionDeny();
            return;
            }
        }
    
        
    
    
        
    // map and edits okay
    // purchases okay
    // all living family members have clear exit paths
    // accept it and check house back in with these changes
    
    
    $query = "UPDATE $tableNamePrefix"."houses SET ".
        "edit_checkout = 0, self_test_running = 0, house_map='$house_map', ".
        "vault_contents='$vault_contents', ".
        "backpack_contents='$backpack_contents', ".
        "gallery_contents='$gallery_contents', ".
        "edit_count='$edit_count', ".
        "self_test_move_list='$self_test_move_list', ".
        "loot_value='$loot_value', value_estimate='$value_estimate', ".
        "wife_present='$wife_present' ".
        "WHERE user_id = $user_id;";
    cd_queryDatabase( $query );

    cd_queryDatabase( "COMMIT;" );
    cd_queryDatabase( "SET AUTOCOMMIT=1" );

    echo "OK";    
    }




function cd_pingHouse() {
    global $tableNamePrefix;

    if( ! cd_verifyTransaction() ) {
        return;
        }

    $user_id = cd_getUserID();

    
    // automatically ignore blocked users and houses not checked out

    $query = "UPDATE $tableNamePrefix"."houses SET ".
        "last_ping_time = CURRENT_TIMESTAMP ".
        "WHERE ( user_id = $user_id OR robbing_user_id = $user_id ) ".
        "AND blocked='0' ".
        "AND ( rob_checkout = 1 OR edit_checkout = 1 );";
    
    $result = cd_queryDatabase( $query );

    
    if( mysql_affected_rows() == 1 ) {
        echo "OK";
        }
    else {
        echo "FAILED";
        }
    }




function cd_startSelfTest() {
    global $tableNamePrefix;

    if( ! cd_verifyTransaction() ) {
        return;
        }

    $user_id = cd_getUserID();

    
    // automatically ignore blocked users and houses not checked out

    $query = "UPDATE $tableNamePrefix"."houses SET ".
        "last_ping_time = CURRENT_TIMESTAMP, self_test_running = 1 ".
        "WHERE user_id = $user_id ".
        "AND blocked='0' ".
        "AND edit_checkout = 1 AND self_test_running = 0;";
    
    $result = cd_queryDatabase( $query );

    
    if( mysql_affected_rows() == 1 ) {
        echo "OK";
        }
    else {
        echo "FAILED";
        }
    }



function cd_endSelfTest() {
    global $tableNamePrefix;

    if( ! cd_verifyTransaction() ) {
        return;
        }

    $user_id = cd_getUserID();

    
    // automatically ignore blocked users and houses not checked out

    $query = "UPDATE $tableNamePrefix"."houses SET ".
        "last_ping_time = CURRENT_TIMESTAMP, self_test_running = 0 ".
        "WHERE user_id = $user_id ".
        "AND blocked='0' ".
        "AND edit_checkout = 1 AND self_test_running = 1;";
    
    $result = cd_queryDatabase( $query );

    
    if( mysql_affected_rows() == 1 ) {
        echo "OK";
        }
    else {
        echo "FAILED";
        }
    }




function cd_listHouses() {
    global $tableNamePrefix;

    if( ! cd_verifyTransaction() ) {
        return;
        }

    $user_id = cd_getUserID();


    $skip = cd_requestFilter( "skip", "/\d+/", 0 );
    
    $limit = cd_requestFilter( "limit", "/\d+/", 20 );
    $name_search = cd_requestFilter( "name_search", "/[a-z]+/i" );

    $searchClause = "";

    if( $name_search != "" ) {
        $searchClause = "AND houses.character_name LIKE '%$name_search%' ";
        }
    
    
    // automatically ignore blocked users and houses already checked
    // out for robbery and houses that haven't been edited yet

    // join to include last robber name for each result
    // (maps each robbing_user_id to the corresponding character_name
    //  by joining the houses table to itself)
    $tableName = $tableNamePrefix ."houses";
    
    $query = "SELECT houses.user_id, houses.character_name, ".
        "houses.value_estimate, houses.rob_attempts, houses.robber_deaths, ".
        "robbers.character_name as robber_name, ".
        "robbers.user_id as robber_id ".
        "FROM $tableName as houses ".
        "LEFT JOIN $tableName as robbers ".
        "ON houses.robbing_user_id = robbers.user_id ".
        "WHERE houses.user_id != '$user_id' AND houses.blocked='0' ".
        "AND houses.rob_checkout = 0 AND houses.edit_checkout = 0 ".
        "AND houses.edit_count > 0 ".
        "$searchClause ".
        "ORDER BY houses.value_estimate DESC, houses.rob_attempts DESC ".
        "LIMIT $skip, $limit;";

    $result = cd_queryDatabase( $query );

    $numRows = mysql_numrows( $result );


    for( $i=0; $i<$numRows; $i++ ) {
        $house_user_id = mysql_result( $result, $i, "user_id" );
        $character_name = mysql_result( $result, $i, "character_name" );
        $robber_name = mysql_result( $result, $i, "robber_name" );
        $robber_id = mysql_result( $result, $i, "robber_id" );
        $value_estimate = mysql_result( $result, $i, "value_estimate" );
        $rob_attempts = mysql_result( $result, $i, "rob_attempts" );
        $robber_deaths = mysql_result( $result, $i, "robber_deaths" );

        if( $robber_name == NULL ) {
            $robber_name = "Null_Null_Null";
            }

        if( $robber_id == $user_id ) {
            // hide name from self
            $robber_name = "You";
            }
        
        echo "$house_user_id#$character_name#$robber_name".
            "#$value_estimate#$rob_attempts#$robber_deaths\n";
        }
    echo "OK";
    }



function cd_startRobHouse() {
    global $tableNamePrefix;

    if( ! cd_verifyTransaction() ) {
        return;
        }

    $user_id = cd_getUserID();

    $to_rob_user_id = cd_requestFilter( "to_rob_user_id", "/\d+/" );
    $to_rob_character_name =
        cd_requestFilter( "to_rob_character_name", "/[A-Z_]+/i" );
    
    cd_queryDatabase( "SET AUTOCOMMIT=0" );

    cd_processStaleCheckouts( $user_id );
    
    // get user's backpack contents
    $query = "SELECT backpack_contents ".
        "FROM $tableNamePrefix"."houses ".
        "WHERE user_id = '$user_id';";

    $result = cd_queryDatabase( $query );

    $backpack_contents = mysql_result( $result, 0, "backpack_contents" );

    
    
    // automatically ignore blocked users and houses already checked
    // out for robbery
    
    $query = "SELECT wife_name, son_name, daughter_name, ".
        "house_map, gallery_contents, ".
        "character_name, rob_attempts, music_seed, wife_present, loot_value ".
        "FROM $tableNamePrefix"."houses ".
        "WHERE user_id = '$to_rob_user_id' AND blocked='0' ".
        "AND edit_checkout = 0 AND rob_checkout = 0 ".
        "FOR UPDATE;";

    $result = cd_queryDatabase( $query );

    $numRows = mysql_numrows( $result );
    
    if( $numRows < 1 ) {
        cd_transactionDeny();
        return;
        }
    $row = mysql_fetch_array( $result, MYSQL_ASSOC );

    $wife_name = $row[ "wife_name" ];
    $son_name = $row[ "son_name" ];
    $daughter_name = $row[ "daughter_name" ];
    
    $house_map = $row[ "house_map" ];
    $gallery_contents = $row[ "gallery_contents" ];
    $character_name = $row[ "character_name" ];
    $music_seed = $row[ "music_seed" ];
    $wife_present = $row[ "wife_present" ];
    $loot_value = $row[ "loot_value" ];
    
    $rob_attempts = $row[ "rob_attempts" ];
    $rob_attempts ++;

    if( $character_name != $to_rob_character_name ) {
        // character names don't match
        // user must have died and respawned as a new character
        // rob request is no longer valid (old house gone)
        echo "RECLAIMED";
        return;
        }
    
    $query = "UPDATE $tableNamePrefix"."houses SET ".
        "rob_checkout = 1, robbing_user_id = '$user_id', ".
        "rob_attempts = '$rob_attempts', last_ping_time = CURRENT_TIMESTAMP ".
        "WHERE user_id = $to_rob_user_id;";
    cd_queryDatabase( $query );

    cd_queryDatabase( "COMMIT;" );
    cd_queryDatabase( "SET AUTOCOMMIT=1" );


    $wife_money = 0;

    if( $wife_present ) {
        $wife_money = (int)( $loot_value / 2 );
        }
    
    echo "$character_name\n";
    echo "$house_map\n";
    echo "$backpack_contents\n";
    echo "$gallery_contents\n";
    echo "$wife_money\n";
    echo "$music_seed\n";
    echo "$wife_name\n";
    echo "$son_name\n";
    echo "$daughter_name\n";
    echo "OK";
    }



function cd_endRobHouse() {
    global $tableNamePrefix;

    if( ! cd_verifyTransaction() ) {
        return;
        }

    $user_id = cd_getUserID();

    $success = cd_requestFilter( "success", "/[012]/" );
    $wife_killed = cd_requestFilter( "wife_killed", "/[01]/" );
    $wife_robbed = cd_requestFilter( "wife_robbed", "/[01]/" );
    $any_family_killed = cd_requestFilter( "any_family_kiled", "/[01]/" );

    $backpack_contents = cd_requestFilter( "backpack_contents", "/[#0-9:]+/" );

    
    cd_queryDatabase( "SET AUTOCOMMIT=0" );



    
    $query = "SELECT backpack_contents, vault_contents, gallery_contents, ".
        "carried_loot_value, carried_vault_contents, ".
        "carried_gallery_contents ". 
        "FROM $tableNamePrefix"."houses ".
        "WHERE user_id = '$user_id' AND blocked='0' FOR UPDATE;";

    $result = cd_queryDatabase( $query );

    $numRows = mysql_numrows( $result );

    if( $numRows < 1 ) {
        cd_transactionDeny();
        cd_log( "Robbery end failed for robber $user_id, ".
                "failed to find robber's house" );
        return;
        }
    
    $old_backpack_contents = mysql_result( $result, 0, "backpack_contents" );

    $old_robber_carried_loot_value =
        mysql_result( $result, 0, "carried_loot_value" );
    $old_robber_carried_vault_contents =
        mysql_result( $result, 0, "carried_vault_contents" );
    $old_robber_carried_gallery_contents =
        mysql_result( $result, 0, "carried_gallery_contents" );



    $move_list = cd_requestFilter( "move_list", "/[mt0-9@#LS]+/" );
    

    // make sure tools used in move_list agrees with change to backpack
    // contents
    $toolsUsedString = "#";

    if( $move_list != "" && $move_list != "#" ) {
        

    
        $toolsUsedArray = array();

        $moves = $pairArray = preg_split( "/#/", $move_list );

        foreach( $moves as $move ) {
            if( $move[0] == 't' ) {
                // tool use

                $parts = preg_split( "/@/", substr( $move, 1 ) );

                if( count( $parts ) != 2 ) {
                    cd_log( "Robbery end with bad move list ".
                            "($move_list) denied" );
                    cd_transactionDeny();
                    return;
                    }
                $tool_id = $parts[0];

                if( array_key_exists( $tool_id, $toolsUsedArray ) ) {
                    $toolsUsedArray[ $tool_id ] ++;
                    }
                else {
                    $toolsUsedArray[ $tool_id ] = 1;
                    }
                }
        
            }

        $toolsUsedString = cd_idQuanityArrayToString( $toolsUsedArray );
        }

    
    $totalBackpack =
        cd_idQuantityUnion( $backpack_contents, $toolsUsedString );

    // make sure $old_backpack_contents is sorted the same way before
    // doing the compare
    $totalBackpackShouldBe =
        cd_idQuantityUnion( $old_backpack_contents, "#" );
    
    
    if( $totalBackpack != $totalBackpackShouldBe ) {
        cd_log( "Robbery end with tools used not adding up with remaining ".
                "backpack contents denied" );
        cd_transactionDeny();
        return;
        }
    
        
    
    
    // automatically ignore blocked users and houses already checked
    // out for robbery
    
    $ownerDied = 0;
    
    $query = "SELECT loot_value, value_estimate, music_seed, wife_present, ".
        "house_map, user_id, character_name, ".
        "wife_name, son_name, daughter_name, ".
        "loot_value, vault_contents, gallery_contents, ".
        "rob_attempts, robber_deaths, edit_count ".
        "FROM $tableNamePrefix"."houses ".
        "WHERE robbing_user_id = '$user_id' AND blocked='0' ".
        "AND rob_checkout = 1 AND edit_checkout = 0 ".
        "FOR UPDATE;";

    $result = cd_queryDatabase( $query );

    $numRows = mysql_numrows( $result );
    
    if( $numRows < 1 ) {
        // not found in main table
        
        // check owner_died table, in case house was flushed while
        // we were robbing it
        $mainTableName = "$tableNamePrefix"."houses";
        $shadowTableName = "$tableNamePrefix"."houses_owner_died";

        // point same query at shadow table
        $query = preg_replace( "/$mainTableName/", "$shadowTableName",
                               $query );

        $result = cd_queryDatabase( $query );
        
        $numRows = mysql_numrows( $result );

        if( $numRows < 1 ) {
            // not found in main table OR shadow table
            
            cd_log( "Robbery end failed for robber $user_id, ".
                "failed to find target house in main or shadow house tables" );

            cd_transactionDeny();
            return;
            }
        else {
            $ownerDied = 1;
            }
        }
    $row = mysql_fetch_array( $result, MYSQL_ASSOC );




    // update contents of backpack (checked to be okay above)
    $query = "UPDATE $tableNamePrefix"."houses SET ".
        "backpack_contents = '$backpack_contents'".
        "WHERE user_id = $user_id;";
    cd_queryDatabase( $query );



    

    
    
    
    $house_map = cd_requestFilter( "house_map", "/[#0-9,:!]+/" );

    $house_money = $row[ "loot_value" ];
    $house_value_estimate = $row[ "value_estimate" ];
    $wife_present = $row[ "wife_present" ];
    $house_vault_contents = $row[ "vault_contents" ];
    $house_gallery_contents = $row[ "gallery_contents" ];
    
    $amountTaken = $house_money;
    $stuffTaken = $house_vault_contents;
    $galleryStuffTaken = $house_gallery_contents;

    
    $old_house_map = $row[ "house_map" ];
    $victim_id = $row[ "user_id" ];
    $victim_name = $row[ "character_name" ];
    $rob_attempts = $row[ "rob_attempts" ];
    $robber_deaths = $row[ "robber_deaths" ];
    $edit_count = $row[ "edit_count" ];
    $music_seed = $row[ "music_seed" ];


    $wife_name = $row[ "wife_name" ];
    $son_name = $row[ "son_name" ];
    $daughter_name = $row[ "daughter_name" ];



    
    // grab past scouting stats here, for inclusion in robbery log
    $query = "SELECT count, last_scout_time ".
        "FROM $tableNamePrefix"."scouting_counts ".
        "WHERE robbing_user_id = '$user_id' ".
        "AND house_user_id = '$victim_id'".
        "FOR UPDATE;";

    $result = cd_queryDatabase( $query );

    $numRows = mysql_numrows( $result );

    $scouting_count = 0;
    $last_scout_time = cd_getMySQLTimestamp();
    
    if( $numRows == 1 ) {
        $scouting_count = mysql_result( $result, 0, "count" );
        $last_scout_time = mysql_result( $result, 0, "last_scout_time" );
        }




    
    
    if( !$any_family_killed && ( $success == 0 || $success == 2 ) ) {
        // keep original house map, untouched
        $house_map = $old_house_map;

        // don't touch loot value
        // or vault
        $amountTaken = 0;
        $stuffTaken = "#";
        $galleryStuffTaken = "#";
        
        if( $success == 0 ) {
            // robber dies, and death count in this house not reset
            $robber_deaths ++;
            }
        }
    else {
        // reached vault, successful robbery, or killed some family members
        
        // use new house map

        // permanent robbery result, has not been edited since
        $edit_count = 0;

        // wife carries half money, if she's there
        $wife_money = (int)( $house_money / 2 );
        if( !$wife_present ) {
            $wife_money = 0;
            }
        
        $vaultMoney = $house_money - $wife_money;

        $amountTaken = 0;

        if( $success != 0 && $wife_robbed ) {
            // robbed wife without dying
            $amountTaken += $wife_money;
            }
        
        if( $success == 1 ) {
            $amountTaken += $vaultMoney;
            }
        else {
            $stuffTaken = "#";
            $galleryStuffTaken = "#";
            }
        
                
        
        // transfer all money and vault stuff from victim to robber
        $carried_loot_value = $amountTaken + $old_robber_carried_loot_value;

        $carried_vault_contents =
            cd_idQuantityUnion( $old_robber_carried_vault_contents,
                                $stuffTaken );
        
        $carried_gallery_contents = $old_robber_carried_gallery_contents;
        
        if( $carried_gallery_contents == "#" ) {
            $carried_gallery_contents = $galleryStuffTaken;
            }
        else {
            if( $galleryStuffTaken != "#" ) {
                // append
                $carried_gallery_contents =
                    $carried_gallery_contents . "#" . $galleryStuffTaken;
                }
            }

        
        // add stuff taken to robber's pending-to-deposit list for
        // vault/gallery
        $query = "UPDATE $tableNamePrefix"."houses SET ".
            "carried_loot_value = $carried_loot_value, ".
            "carried_vault_contents = '$carried_vault_contents', ".
            "carried_gallery_contents = '$carried_gallery_contents' ".
            "WHERE user_id = $user_id;";
        cd_queryDatabase( $query );


        // log robbery
        $robber_name = cd_getCharacterName( $user_id );

        $loadout = $old_backpack_contents;

        // in log, value_estimate holds true value of stuff taken
        $total_value_stolen = $amountTaken +
            cd_idQuantityToResaleValue( $stuffTaken, cd_getPriceArray() );

        // log_id auto-assigned
        $query =
            "INSERT INTO $tableNamePrefix"."robbery_logs ".
            "(user_id, house_user_id, loot_value, wife_money, ".
            "value_estimate, ".
            " vault_contents, gallery_contents, ".
            " music_seed, ".
            " rob_attempts, robber_deaths,".
            " robber_name, victim_name,".
            " wife_name, son_name, daughter_name,".
            " owner_now_dead, rob_time,".
            " scouting_count, last_scout_time, ".
            " house_start_map, loadout, move_list, house_end_map ) ".
            "VALUES(" .
            " $user_id, $victim_id, '$house_money', '$wife_money', ".
            "'$total_value_stolen', ".
            " '$house_vault_contents', '$house_gallery_contents', ".
            " '$music_seed', ".
            " '$rob_attempts', '$robber_deaths', ".
            " '$robber_name', '$victim_name',".
            " '$wife_name', '$son_name', '$daughter_name',".
            " '$ownerDied', CURRENT_TIMESTAMP,".
            " '$scouting_count', '$last_scout_time', ".
            " '$old_house_map', '$loadout', '$move_list', ".
            " '$house_map' );";
        cd_queryDatabase( $query );

        // some (or all) loot taken
        $house_money -= $amountTaken;

        if( $success ) {
            // reached vault, stole everything there too    
            $house_vault_contents = "#";
            $house_gallery_contents = "#";
            }
        
        

        // clear rob stats now, because house loot is gone
        // rob stats will build up again if loot ever replentished
        // rob stats indicate how hard the current configuraiton is,
        // not the full history of the house
        $rob_attempts = 0;
        $robber_deaths = 0;
        }

    
    if( $success == 0 ) {
        // drops backpack in this house's vault
        $house_vault_contents = cd_idQuantityUnion( $house_vault_contents,
                                                    $backpack_contents );
        
        // starts over as new character, house destroyed
        cd_newHouseForUser( $user_id );
        }


    
    
    // all cases (death, reach vault, leave) provide scouting info
    // and count as a scouting trip
    $scouting_count++;

    if( $scouting_count > 1 ) {
        $query = "UPDATE $tableNamePrefix"."scouting_counts ".
            "SET count = '$scouting_count', ".
            "last_scout_time = CURRENT_TIMESTAMP ".
            "WHERE robbing_user_id = '$user_id' ".
            "AND house_user_id = '$victim_id';";
        cd_queryDatabase( $query );
        }
    else {
        // first scouting trip
        $query =
            "INSERT INTO $tableNamePrefix"."scouting_counts ".
            "( robbing_user_id, house_user_id, count, last_scout_time ) ".
            "VALUES( '$user_id', '$victim_id', ".
            "        '$scouting_count', CURRENT_TIMESTAMP );";
        cd_queryDatabase( $query );
        }
    


    
    
        
    if( ! $ownerDied ) {
        if( $wife_killed ) {
            $wife_present = 0;
            }
        
        // update main table with changes, post-robbery
        $query = "UPDATE $tableNamePrefix"."houses SET ".
            "rob_checkout = 0, edit_count = '$edit_count', ".
            "rob_attempts = '$rob_attempts', ".
            "robber_deaths = '$robber_deaths',".
            "house_map='$house_map', ".
            "loot_value = $house_money,  ".
            "wife_present = $wife_present,  ".
            "vault_contents = '$house_vault_contents', ".
            "gallery_contents = '$house_gallery_contents' ".
            "WHERE robbing_user_id = $user_id AND rob_checkout = 1;";
        cd_queryDatabase( $query );
        }
    else {
        // owner died elsewhere while we were robbing, house
        // scheduled to be destroyed
        
        // simply remove this house from the shadow table
        $query = "DELETE FROM $tableNamePrefix"."houses_owner_died WHERE ".
            " robbing_user_id = $user_id;";
        cd_queryDatabase( $query );

        // clear scouting counts for every robber, since house gone
        $query = "DELETE FROM $tableNamePrefix"."scouting_counts WHERE ".
            " house_user_id = $victim_id;";
        cd_queryDatabase( $query );

        
        // return any remaining gallery stuff to auction house
        // (this will be an empty return if robbery successful)
        cd_returnGalleryContents( $house_gallery_contents );
        }
    
    cd_queryDatabase( "COMMIT;" );
    cd_queryDatabase( "SET AUTOCOMMIT=1" );

    echo "$amountTaken\n";
    echo "$stuffTaken\n";
    echo "$galleryStuffTaken\n";
    echo "OK";
    }




function cd_listLoggedRobberies() {
    global $tableNamePrefix;

    if( ! cd_verifyTransaction() ) {
        return;
        }

    $user_id = cd_getUserID();
    $admin = cd_isAdmin( $user_id );

    $skip = cd_requestFilter( "skip", "/\d+/", 0 );
    
    $limit = cd_requestFilter( "limit", "/\d+/", 20 );

    $name_search = cd_requestFilter( "name_search", "/[a-z]+/i" );


    // get user's current character name
    $character_name = cd_getCharacterName( $user_id );
    
    
    
    $whereClause = "";
    $specificUserClause = "";
    $searchClause = "";

    if( $name_search != "" ) {
        $searchClause = " robber_name LIKE '%$name_search%' ";
        }
    if( ! $admin ) {
        $specificUserClause =
            " house_user_id = '$user_id' AND ".
            " victim_name = '$character_name' ";
        }
    if( $searchClause != "" || $specificUserClause != "" ) {
        $whereClause = " WHERE $searchClause ";
        if( $searchClause != "" && $specificUserClause != "" ) {
            $whereClause = $whereClause . "AND ";
            }
        $whereClause = $whereClause . " $specificUserClause ";
        }
    


    
    $tableName = $tableNamePrefix ."robbery_logs";
    
    $query = "SELECT user_id, house_user_id, ".
        "log_id, victim_name, robber_name, ".
        "value_estimate, rob_attempts, robber_deaths ".
        "FROM $tableName ".
        "$whereClause ".
        "ORDER BY rob_time DESC ".
        "LIMIT $skip, $limit;";

    $result = cd_queryDatabase( $query );

    $numRows = mysql_numrows( $result );


    for( $i=0; $i<$numRows; $i++ ) {
        $robber_id = mysql_result( $result, $i, "user_id" );
        $victim_id = mysql_result( $result, $i, "house_user_id" );

        $log_id = mysql_result( $result, $i, "log_id" );
        $victim_name = mysql_result( $result, $i, "victim_name" );
        $robber_name = mysql_result( $result, $i, "robber_name" );
        $value_estimate = mysql_result( $result, $i, "value_estimate" );
        $rob_attempts = mysql_result( $result, $i, "rob_attempts" );
        $robber_deaths = mysql_result( $result, $i, "robber_deaths" );

        if( $robber_id == $user_id ) {
            $robber_name = "You";
            }
        if( $victim_id == $user_id ) {
            $victim_name = "You";
            }
        
        
        echo "$log_id#$victim_name#$robber_name".
            "#$value_estimate#$rob_attempts#$robber_deaths\n";
        }
    echo "OK";
    }



function cd_getRobberyLog() {
    global $tableNamePrefix;

    if( ! cd_verifyTransaction() ) {
        return;
        }


    $user_id = cd_getUserID();
    $admin = cd_isAdmin( $user_id );
    

    $log_id = cd_requestFilter( "log_id", "/\d+/" );
    
    
    
    $query = "SELECT user_id, house_user_id, ".
        "robber_name, victim_name, ".
        "wife_name, son_name, daughter_name, ".
        "house_start_map, loadout, ".
        "move_list, value_estimate, wife_money, music_seed ".
        "FROM $tableNamePrefix"."robbery_logs ".
        "WHERE log_id = '$log_id';";

    $result = cd_queryDatabase( $query );

    $numRows = mysql_numrows( $result );
    
    if( $numRows < 1 ) {
        cd_transactionDeny();
        return;
        }
    $row = mysql_fetch_array( $result, MYSQL_ASSOC );


    $robber_name = $row[ "robber_name" ];
    $victim_name = $row[ "victim_name" ];
    
    if( !$admin ) {
        // if NOT admin
        // don't allow user to obtain someone else's log
        // OR a log for their past life
        if( $user_id != $row[ "house_user_id" ] ||
            $victim_name != cd_getCharacterName( $user_id ) ) {
            cd_transactionDeny();
            return;
            }
        }
    
    
    if( $user_id == $row[ "user_id" ] ) {
        $robber_name = "You";
        }

    if( $user_id == $row[ "house_user_id" ] ) {
        $victim_name = "You";
        }
    
    
    
    echo $robber_name . "\n";    
    echo $victim_name . "\n";    
    echo $row[ "house_start_map" ] . "\n";    
    echo $row[ "loadout" ] . "\n";    
    echo $row[ "move_list" ] . "\n";
    echo $row[ "value_estimate" ] . "\n";
    echo $row[ "wife_money" ] . "\n";
    echo $row[ "music_seed" ] . "\n";
    echo $row[ "wife_name" ] . "\n";
    echo $row[ "son_name" ] . "\n";
    echo $row[ "daughter_name" ] . "\n";
    echo "OK";
    }




function cd_getSelfTestLog() {
    global $tableNamePrefix;

    if( ! cd_verifyTransaction() ) {
        return;
        }


    $user_id = cd_getUserID();
    if( !cd_isAdmin( $user_id ) ) {
        cd_log( "Non-admin user $user_id tried to view a self-test log." );
        cd_transactionDeny();
        return;
        }
    

    $house_owner_id = cd_requestFilter( "house_owner_id", "/\d+/" );
    
    
    
    $query = "SELECT character_name, ".
        "wife_name, son_name, daughter_name, ".
        "house_map, self_test_move_list, wife_present, ".
        "loot_value, music_seed ".
        "FROM $tableNamePrefix"."houses ".
        "WHERE user_id = '$house_owner_id';";

    $result = cd_queryDatabase( $query );

    $numRows = mysql_numrows( $result );
    
    if( $numRows < 1 ) {
        cd_transactionDeny();
        return;
        }
    $row = mysql_fetch_array( $result, MYSQL_ASSOC );


    $wife_money = 0;

    if( $row[ "wife_present" ] ) {
        $wife_money = (int)( $row[ "loot_value" ] / 2 );
        }
    
    echo $row[ "character_name" ] . "\n";
    echo $row[ "house_map" ] . "\n";
    echo $row[ "self_test_move_list" ] . "\n";
    echo $wife_money. "\n";
    echo $row[ "music_seed" ] . "\n";
    echo $row[ "wife_name" ] . "\n";
    echo $row[ "son_name" ] . "\n";
    echo $row[ "daughter_name" ] . "\n";

    echo "OK";
    }




function cd_computeAuctionPrice( $start_price, $elapsed_seconds ) {
    global $auctionPriceDropInterval, $auctionPriceHalfLife;

    $priceDropIntervalSeconds = $auctionPriceDropInterval * 60;
    
    
    $intervalsPerHalfLife = $auctionPriceHalfLife / $auctionPriceDropInterval;

    // want D ^ ($intervalsPerHalfLife) = 0.5;
    // where D is the price shrink factor per interval
    // $D = pow( 0.5, 1 / $intervalsPerHalfLife );

    // but we're just going to raise D ^ (numIntervalsPassed) anyway
    // so might as well just rais 0.5 to the multiplied power instead
    
    $numIntervalsPassed =
        floor( $elapsed_seconds / $priceDropIntervalSeconds );
    
    $price = floor( $start_price *
                    pow( 0.5, $numIntervalsPassed / $intervalsPerHalfLife ) );

    if( $price < 1 ) {
        $price = 1;
        }
    
    return $price;
    }



function cd_listAuctions() {
    global $tableNamePrefix;

    if( ! cd_verifyTransaction() ) {
        return;
        }

    $tableName = $tableNamePrefix ."auction";
    
    $query = "SELECT object_id, start_price, ".
        "TIMESTAMPDIFF( SECOND, start_time, CURRENT_TIMESTAMP ) ".
        "   as elapsed_seconds ".
        "FROM $tableName ".
        "ORDER BY elapsed_seconds DESC, order_number ASC;";

    $result = cd_queryDatabase( $query );

    $numRows = mysql_numrows( $result );

    $seconds_until_price_drop = 0;

    global $auctionPriceDropInterval, $auctionPriceHalfLife;

    $priceDropIntervalSeconds = $auctionPriceDropInterval * 60;
    
    if( $numRows > 0 ) {
        $elapsed_seconds = mysql_result( $result, 0, "elapsed_seconds" );

        // subtract whole multiple of 3 minutes
        $seconds_until_price_drop =
            $priceDropIntervalSeconds -
            $elapsed_seconds % $priceDropIntervalSeconds;
        
        }

    echo "$seconds_until_price_drop\n";
    
    for( $i=0; $i<$numRows; $i++ ) {
        $object_id = mysql_result( $result, $i, "object_id" );
        $start_price = mysql_result( $result, $i, "start_price" );
        $elapsed_seconds = mysql_result( $result, $i, "elapsed_seconds" );


        $price = cd_computeAuctionPrice( $start_price, $elapsed_seconds );
                
        echo "$object_id#$price\n";
        }
    echo "OK";
    }



function cd_buyAuction() {
    global $tableNamePrefix;

    if( ! cd_verifyTransaction() ) {
        return;
        }


    $user_id = cd_getUserID();


    $object_id = cd_requestFilter( "object_id", "/\d+/" );
    
    
    
    $auctionTable = $tableNamePrefix ."auction";
    $houseTable = $tableNamePrefix ."houses";

    // make sure it's there, and check it's current price
    $query = "SELECT start_price, ".
        "TIMESTAMPDIFF( SECOND, start_time, CURRENT_TIMESTAMP ) ".
        "   as elapsed_seconds ".
        "FROM $auctionTable WHERE object_id = '$object_id' FOR UPDATE;";

    $result = cd_queryDatabase( $query );

    $numRows = mysql_numrows( $result );

    if( $numRows < 1 ) {
        cd_transactionDeny();
        return;
        }

    $start_price = mysql_result( $result, 0, "start_price" );
    $elapsed_seconds = mysql_result( $result, 0, "elapsed_seconds" );
    
    $price = cd_computeAuctionPrice( $start_price, $elapsed_seconds );


    // make sure user has enough balance in house, and house checked out

    $query = "SELECT gallery_contents, loot_value ".
        "FROM $houseTable ".
        "WHERE user_id = '$user_id' AND blocked='0' ".
        "AND edit_checkout = 1 FOR UPDATE;";

    $result = cd_queryDatabase( $query );

    $numRows = mysql_numrows( $result );
    
    if( $numRows < 1 ) {
        cd_transactionDeny();
        return;
        }

    $old_balance = mysql_result( $result, 0, "loot_value" );

    if( $old_balance < $price ) {
        cd_transactionDeny();
        return;
        }

    // user has enough money!
    $new_balance = $old_balance - $price;

    $old_gallery_contents = mysql_result( $result, 0, "gallery_contents" );
    $new_gallery_contents = "";
    
    if( $old_gallery_contents == "#" ) {
        // empty
        $new_gallery_contents = "$object_id";
        }
    else {
        // prepend
        $new_gallery_contents = "$object_id#". $old_gallery_contents;
        }


    
    // now make changes

    $query = "UPDATE $houseTable SET ".
        "loot_value = '$new_balance', ".
        "gallery_contents = '$new_gallery_contents' ".
        "WHERE user_id = '$user_id';";

    $result = cd_queryDatabase( $query );


    $query = "DELETE FROM $auctionTable WHERE object_id = '$object_id';";
    
    $result = cd_queryDatabase( $query );


    cd_queryDatabase( "COMMIT;" );
    cd_queryDatabase( "SET AUTOCOMMIT=1" );

    echo "$price\n";
    echo "OK";
    }
















// utility function for stuff common to all denied user transactions
function cd_transactionDeny() {
    echo "DENIED";
    
    cd_queryDatabase( "COMMIT;" );
    cd_queryDatabase( "SET AUTOCOMMIT=1" );
    }



function cd_getCharacterName( $user_id ) {
    global $tableNamePrefix;
    
    $result = cd_queryDatabase( "SELECT character_name ".
                                "FROM $tableNamePrefix"."houses " .
                                "WHERE user_id = $user_id;" );

    $numRows = mysql_numrows( $result );

    
    if( $numRows < 1 ) {
        cd_fatalError( "Failed to fetch character name for user $user_id" );
        }

    return mysql_result( $result, 0, 0 );
    }



function cd_getUserID() {
    return cd_requestFilter( "user_id", "/\d+/" );
    }



// checks the ticket HMAC for the user ID and sequence number
// attached to a transaction (also makes sure user isn't blocked!)
function cd_verifyTransaction() {
    global $tableNamePrefix;
    
    $user_id = cd_getUserID();

    $sequence_number = cd_requestFilter( "sequence_number", "/\d+/" );

    $ticket_hmac = cd_requestFilter( "ticket_hmac", "/[A-F0-9]+/i" );
    

    cd_queryDatabase( "SET AUTOCOMMIT=0" );

    // automatically ignore blocked users
    
    $query = "SELECT sequence_number, ticket_id ".
        "FROM $tableNamePrefix"."users ".
        "WHERE user_id = '$user_id' AND blocked='0' FOR UPDATE;";

    $result = cd_queryDatabase( $query );


    $numRows = mysql_numrows( $result );

    
    if( $numRows < 1 ) {
        cd_transactionDeny();
        cd_log( "Transaction denied for user_id $user_id, ".
                "not found or blocked" );
        return 0;
        }
    
    $row = mysql_fetch_array( $result, MYSQL_ASSOC );

    $last_sequence_number = $row[ "sequence_number" ];

    if( $sequence_number < $last_sequence_number ) {
        cd_transactionDeny();
        cd_log( "Transaction denied for user_id $user_id, ".
                "stale sequence number" );
        return 0;
        }
    
    
    
    $ticket_id = $row[ "ticket_id" ];


    $correct_ticket_hmac = cd_hmac_sha1( $ticket_id, "$sequence_number" );


    if( strtoupper( $correct_ticket_hmac ) !=
        strtoupper( $ticket_hmac ) ) {
        cd_transactionDeny();
        cd_log( "Transaction denied for user_id $user_id, ".
                "hmac check failed" );

        return 0;
        }

    // sig passed, sequence number not a replay!

    // update the sequence number, which we have locked

    $new_number = $sequence_number + 1;
    
    $query = "UPDATE $tableNamePrefix"."users SET ".
        "sequence_number = $new_number ".
        "WHERE user_id = $user_id;";
    cd_queryDatabase( $query );

    cd_queryDatabase( "COMMIT;" );
    cd_queryDatabase( "SET AUTOCOMMIT=1" );

    return 1;
    }




function cd_returnGalleryContents( $gallery_contents ) {
    global $tableNamePrefix;

    if( $gallery_contents != "#" ) {

        $galleryArray = preg_split( "/#/", $gallery_contents );

        foreach( $galleryArray as $galleryID ) {                
            
            $query = "SELECT order_number, price FROM ".
                "$tableNamePrefix"."prices WHERE ".
                "in_gallery = 1 AND object_id = '$galleryID';";
    
            $result = cd_queryDatabase( $query );
            
            $numRows = mysql_numrows( $result );

            if( $numRows > 0 ) {
                $order_number = mysql_result( $result, 0, "order_number" );
                $price = mysql_result( $result, 0, "price" );
                
                cd_startAuction( $galleryID, $order_number, $price );
                }
            }
        }
    }




function cd_isAdmin( $user_id ) {
    
    global $tableNamePrefix;
    
    $query = "SELECT admin FROM ".
        "$tableNamePrefix"."users WHERE ".
        "user_id = '$user_id';";
    
    $result = cd_queryDatabase( $query );
    $numRows = mysql_numrows( $result );

    if( $numRows > 0 ) {
        return ( mysql_result( $result, 0, "admin" ) == 1 );
        }
    else {
        return false;
        }
    }




function cd_newHouseForUser( $user_id ) {
    global $tableNamePrefix;
    
    
    // create default house for user

    $ticket_id = "";
    $email = "";
    $character_name_history = "";
    
    $query = "select ticket_id, email, character_name_history ".
        "FROM $tableNamePrefix"."users ".
        "WHERE user_id = $user_id;";
    $result = cd_queryDatabase( $query );
    
    $numRows = mysql_numrows( $result );

    if( $numRows > 0 ) {
        $ticket_id = mysql_result( $result, 0, "ticket_id" );
        $character_name_history =
            mysql_result( $result, 0, "character_name_history" );
        $email = mysql_result( $result, 0, "email" );
        }
    
    

    cd_queryDatabase( "SET AUTOCOMMIT = 0;" );

    // select first, for update, so we can safely delete old house
    // if there is one
    // NOTE that if house doesn't exist, this select will NOT block the
    // row gap.  In the case of concurrent inserts for the same user_id,
    // the second insert will fail (user_id is the primary key)
    
    $query = "select user_id, gallery_contents, rob_checkout ".
        "FROM $tableNamePrefix"."houses ".
        "WHERE user_id = $user_id ".
        "FOR UPDATE;";

    $result = cd_queryDatabase( $query );

    $numRows = mysql_numrows( $result );

    if( $numRows > 0 ) {

        // user had a house (past life)

        // is anyone still robbing it?
        $rob_checkout = mysql_result( $result, 0, "rob_checkout" );

        if( $rob_checkout ) {

            // can't delete it and leave robber hanging

            // copy it into temporary storage table

            
            $query = "INSERT INTO $tableNamePrefix"."houses_owner_died ".
                "SELECT * FROM $tableNamePrefix"."houses ".
                "WHERE user_id = $user_id;";

            cd_queryDatabase( $query );
            
            // return gallery stuff to auction house later,
            // and clear scouting counts later, after this robber done
            }
        else {

            // clear scouting counts for every robber, since house gone
            $query = "DELETE FROM $tableNamePrefix"."scouting_counts WHERE ".
                " house_user_id = $user_id;";
            cd_queryDatabase( $query );

            
            // return gallery items to auciton house
            $gallery_contents = mysql_result( $result, 0, "gallery_contents" );
            cd_returnGalleryContents( $gallery_contents );
            }

        
        // in either case, delete house from main tables
        
        $query = "delete from $tableNamePrefix"."houses ".
            "WHERE user_id = $user_id;";
        cd_queryDatabase( $query );
        }
    
    
    // Generate a unique name here
    // Error number generated when a forced-unique key already exists upon
    // insertion
    // Best way to ensure that character names are unique, and keep
    // searching for a unique one after a collision.
    $errorNumber = 1062;    
    $foundName = false;
    

    global $wifeList, $sonList, $daughterList;

    $pickedWife = $wifeList[ array_rand( $wifeList, 1 ) ];
    $pickedSon = $sonList[ array_rand( $sonList, 1 ) ];
    $pickedDaughter = $daughterList[ array_rand( $daughterList, 1 ) ];
    
    
    // default house map, 32x32 map
    // impenetrable walls around exterior
    // goal in place
    // default state for each cell (no ":state" part)
    $house_map =
        "998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#".
        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".
        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".
        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".

        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".
        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".
        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".
        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".

        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".
        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".
        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".
        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".

        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".
        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".
        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".
      "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".

        
        "0#0#0#0#0#0#0#0#0#999#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".
        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".
        "998#0#0#0#0#0#$pickedSon#0#$pickedWife#0#$pickedDaughter#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".
        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".

        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".
        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".
        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".
        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".

        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".
        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".
        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".
        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".

        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".
        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".
        "998#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#998#".
        "998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998#998";
    
    $vault_contents = "#";
    $backpack_contents = "#";
    $gallery_contnets = '#';

    $carried_loot_value = 0;
    $carried_vault_contents = "#";
    $carried_gallery_contents = "#";

    $music_seed = mt_rand();

    $wife_name = cd_pickName( "wife_names" );
    $son_name = cd_pickName( "son_names" );

    // make sure daughter has name unique from wife
    $daughter_name = $wife_name;
    while( $daughter_name == $wife_name ) {
        $daughter_name = cd_pickName( "daughter_names" );
        }
    
    while( !$foundName && $errorNumber == 1062 ) {
        $character_name = cd_pickFullName();
        
        
        $query = "INSERT INTO $tableNamePrefix"."houses VALUES(" .
            " $user_id, '$character_name', ".
            "'$wife_name', '$son_name', '$daughter_name', ".
            "'$house_map', ".
            "'$vault_contents', '$backpack_contents', '$gallery_contnets', ".
            "'$music_seed', ".
            "0, '#', 1000, 1000, 1, ".
            "'$carried_loot_value', '$carried_vault_contents', ".
            "'$carried_gallery_contents', ".
            "0, 0, 0, 0, 0, 0, ".
            "CURRENT_TIMESTAMP, 0 );";

        // bypass our default error handling here so that
        // we can react to duplicate errors
        $result = mysql_query( $query );

        if( $result ) {
            $foundName = true;

            // prepend this name to name history
            $character_name_history =
                $character_name . " " .$character_name_history;
            $query = "UPDATE $tableNamePrefix"."users SET ".
                "character_name_history = '$character_name_history' ".
                "WHERE user_id = '$user_id';";
            cd_queryDatabase( $query );

            // flag any robbery logs from past lives
            $query = "UPDATE $tableNamePrefix"."robbery_logs SET ".
                "owner_now_dead = 1 ".
                "WHERE house_user_id = '$user_id' ".
                "AND victim_name != '$character_name' ".
                "AND owner_now_dead = 0;";
            cd_queryDatabase( $query );
            }
        else {
            $errorNumber = mysql_errno();

            cd_log( "Name collision for $character_name?  ".
                    "Error: $errorNumber" );

            if( $errorNumber != 1062 ) {
                cd_fatalError(
                    "Database query failed:<BR>$query<BR><BR>" .
                    mysql_error() );
                }
            }
        }
    
    cd_queryDatabase( "COMMIT;" );
    
    
    cd_queryDatabase( "SET AUTOCOMMIT = 1;" );
    }




function cd_logout() {

    cd_clearPasswordCookie();

    echo "Logged out";
    }




function orderLink( $inOrderBy, $inLinkText ) {
        global $skip, $search, $order_by;
        if( $inOrderBy == $order_by ) {
            // already displaying this order, don't show link
            return "<b>$inLinkText</b>";
            }

        // else show a link to switch to this order
        return "<a href=\"server.php?action=show_data" .
            "&search=$search&skip=$skip&order_by=$inOrderBy\">$inLinkText</a>";
        }


function cd_showDataHouseList( $inTableName ) {
    global $tableNamePrefix;
    
    // these are global so they work in embeded function call below
    global $skip, $search, $order_by;

    $skip = cd_requestFilter( "skip", "/\d+/", 0 );

    global $housesPerPage;
    
    $search = cd_requestFilter( "search", "/[A-Z0-9_@. -]+/i" );

    $order_by = cd_requestFilter( "order_by", "/[A-Z_]+/i", "last_ping_time" );
    

    $keywordClause = "";
    $searchDisplay = "";
    
    if( $search != "" ) {
        

        $keywordClause = "WHERE ( user_id LIKE '%$search%' " .
            "OR character_name LIKE '%$search%' ".
            "OR loot_value LIKE '%$search%' ".
            "OR email LIKE '%$search%' OR ticket_id LIKE '%$search%' ) ";

        $searchDisplay = " matching <b>$search</b>";
        }
    

    $houseTable = "$tableNamePrefix"."$inTableName";
    $usersTable = "$tableNamePrefix"."users";
    
    

    // first, count results
    $query = "SELECT COUNT(*) ".
        "FROM $houseTable INNER JOIN $usersTable ".
        "ON $houseTable.user_id = $usersTable.user_id $keywordClause;";

    $result = cd_queryDatabase( $query );
    $totalHouses = mysql_result( $result, 0, 0 );

    
             
    $query = "SELECT $houseTable.user_id, character_name, ".
        "loot_value, edit_checkout, ".
        "self_test_running, rob_checkout, robbing_user_id, rob_attempts, ".
        "robber_deaths, last_ping_time, ".
        "$houseTable.blocked ".
        "FROM $houseTable INNER JOIN $usersTable ".
        "ON $houseTable.user_id = $usersTable.user_id $keywordClause ".
        "ORDER BY $order_by DESC ".
        "LIMIT $skip, $housesPerPage;";
    $result = cd_queryDatabase( $query );
    
    $numRows = mysql_numrows( $result );

    $startSkip = $skip + 1;
    
    $endSkip = $startSkip + $housesPerPage - 1;

    if( $endSkip > $totalHouses ) {
        $endSkip = $totalHouses;
        }





    

    
    echo "$totalHouses active houses". $searchDisplay .
        " (showing $startSkip - $endSkip):<br>\n";

    
    $nextSkip = $skip + $housesPerPage;

    $prevSkip = $skip - $housesPerPage;
    
    if( $prevSkip >= 0 ) {
        echo "[<a href=\"server.php?action=show_data" .
            "&skip=$prevSkip&search=$search&order_by=$order_by\">".
            "Previous Page</a>] ";
        }
    if( $nextSkip < $totalHouses ) {
        echo "[<a href=\"server.php?action=show_data" .
            "&skip=$nextSkip&search=$search&order_by=$order_by\">".
            "Next Page</a>]";
        }

    echo "<br><br>";
    
    echo "<table border=1 cellpadding=5>\n";


    
    
    echo "<tr>\n";
    echo "<td>".orderLink( "user_id", "User ID" )."</td>\n";
    echo "<td>Blocked?</td>\n";
    echo "<td>".orderLink( "character_name", "Character Name" )."</td>\n";
    echo "<td>".orderLink( "loot_value", "Loot Value" )."</td>\n";
    echo "<td>".orderLink( "rob_attempts", "Rob Attempts" )."</td>\n";
    echo "<td>".orderLink( "robber_deaths", "Deaths" )."</td>\n";
    echo "<td>Checkout?</td>\n";
    echo "<td>Robbing User</td>\n";
    echo "<td>".orderLink( "last_ping_time", "PingTime" )."</td>\n";

    echo "</tr>\n";
    

    for( $i=0; $i<$numRows; $i++ ) {
        $user_id = mysql_result( $result, $i, "user_id" );
        $character_name = mysql_result( $result, $i, "character_name" );
        $loot_value = mysql_result( $result, $i, "loot_value" );
        $edit_checkout = mysql_result( $result, $i, "edit_checkout" );
        $self_test_running = mysql_result( $result, $i, "self_test_running" );
        $rob_checkout = mysql_result( $result, $i, "rob_checkout" );
        $robbing_user_id = mysql_result( $result, $i, "robbing_user_id" );
        $rob_attempts = mysql_result( $result, $i, "rob_attempts" );
        $robber_deaths = mysql_result( $result, $i, "robber_deaths" );
        $last_ping_time = mysql_result( $result, $i, "last_ping_time" );
        $blocked = mysql_result( $result, $i, "blocked" );
        
        $block_toggle = "";
        
        if( $blocked ) {
            $blocked = "BLOCKED";
            $block_toggle = "<a href=\"server.php?action=block_user_id&".
                "blocked=0&user_id=$user_id".
                "&search=$search&skip=$skip&order_by=$order_by\">unblock</a>";
            
            }
        else {
            $blocked = "";
            $block_toggle = "<a href=\"server.php?action=block_user_id&".
                "blocked=1&user_id=$user_id".
                "&search=$search&skip=$skip&order_by=$order_by\">block</a>";
            
            }

        $checkout = " ";

        if( $edit_checkout ) {
            if( $self_test_running ) {
                $checkout = "edit (self-test)";
                }
            else {
                $checkout = "edit";
                }
            }
        if( $rob_checkout ) {
            $checkout = "rob";
            }
        if( $edit_checkout && $rob_checkout ) {
            $checkout = "BOTH!";
            }
        

        
        echo "<tr>\n";
        
        echo "<td><b>$user_id</b> ";
        echo "[<a href=\"server.php?action=show_detail" .
            "&user_id=$user_id\">detail</a>]</td>\n";
        echo "<td align=right>$blocked [$block_toggle]</td>\n";
        echo "<td>$character_name</td>\n";
        echo "<td>$loot_value</td>\n";
        echo "<td>$rob_attempts</td>\n";
        echo "<td>$robber_deaths</td>\n";
        echo "<td>$checkout</td>\n";
        echo "<td>$robbing_user_id ";
        echo "[<a href=\"server.php?action=show_detail" .
            "&user_id=$robbing_user_id\">detail</a>]</td>\n";
        echo "<td>$last_ping_time</td>\n";
        echo "</tr>\n";
        }
    echo "</table>";
    }




function cd_formatBytes( $inNumBytes ) {
    
    $sizeString = "";

    if( $inNumBytes <= 1024 ) {
        $sizeString = "$inNumBytes B";
        }
    else if( $inNumBytes > 1024 ) {
        $sizeString = sprintf( "%.2f KiB", $inNumBytes / 1024 );
        }
    else if( $inNumBytes > 1024 * 1024 ) {
        $sizeString = sprintf( "%.2f MiB", $inNumBytes / ( 1024 * 1024 ) );
        }
    return $sizeString;
    }


function cd_generateHeader() {
    $bytesUsed = cd_getTotalSpace();

    $sizeString = cd_formatBytes( $bytesUsed );

    $userCount = cd_countUsers();

    $perUserString = "?";
    if( $userCount > 0 ) {
        $perUserString = cd_formatBytes( $bytesUsed / $userCount );
        }
    
    
    echo "<table width='100%' border=0><tr>".
        "<td>[<a href=\"server.php?action=show_data" .
            "\">Main</a>] ".
            "[<a href=\"server.php?action=show_prices" .
            "\">Prices</a>]</td>".
        "<td align=center>$sizeString ($perUserString per user)</td>".
        "<td align=right>[<a href=\"server.php?action=logout" .
            "\">Logout</a>]</td>".
        "</tr></table><br><br><br>";
    }



function cd_showData() {

    global $tableNamePrefix, $remoteIP;


    cd_checkPassword( "show_data" );

    
    cd_generateHeader();
    
    
    $search = cd_requestFilter( "search", "/[A-Z0-9_@. -]+/i" );
    $order_by = cd_requestFilter( "order_by", "/[A-Z_]+/i", "last_ping_time" );
    
    // form for searching houses
?>
        <hr>
            <FORM ACTION="server.php" METHOD="post">
    <INPUT TYPE="hidden" NAME="action" VALUE="show_data">
    <INPUT TYPE="hidden" NAME="order_by" VALUE="<?php echo $order_by;?>">
    <INPUT TYPE="text" MAXLENGTH=40 SIZE=20 NAME="search"
             VALUE="<?php echo $search;?>">
    <INPUT TYPE="Submit" VALUE="Search">
    </FORM>
        <hr>
<?php

             
    cd_showDataHouseList( "houses" );
    



    echo "<hr>";


    $query = "SELECT COUNT(*) ".
        "FROM $tableNamePrefix"."houses_owner_died;";

    $result = cd_queryDatabase( $query );
    $totalShadowHouses = mysql_result( $result, 0, 0 );

    if( $totalShadowHouses > 0 ) {
        echo "<b>Shadow houses</b>:<br>";

        cd_showDataHouseList( "houses_owner_died" );

        echo "<hr>";
        }


    echo "<hr>";

    echo "<a href=\"server.php?action=show_log\">".
        "Show log</a>";
    echo "<hr>";
    echo "Generated for $remoteIP\n";
    
    }


    
function cd_showPrices() {
    global $tableNamePrefix, $remoteIP;


    cd_checkPassword( "show_prices" );

    cd_generateHeader();
    
    $query = "SELECT object_id, price, in_gallery, order_number, note ".
        "FROM $tableNamePrefix"."prices ORDER BY order_number;";
    $result = cd_queryDatabase( $query );
    
    $numRows = mysql_numrows( $result );

?>
    <br>
    <a name="priceList">      
    <FORM ACTION="server.php#priceList" METHOD="post">
    <INPUT TYPE="Submit" VALUE="Update Prices">
    <INPUT TYPE="hidden" NAME="action" VALUE="update_prices">
    <INPUT TYPE="hidden" NAME="num_prices" VALUE="<?php echo $numRows;?>">
<?php

    echo "<table border=1>\n";

    $bgColor = "#EEEEEE";
    $altBGColor = "#CCCCCC";

    $max_order_number = 0;
    
    for( $i=0; $i<$numRows; $i++ ) {
        $object_id = mysql_result( $result, $i, "object_id" );
        $price = mysql_result( $result, $i, "price" );
        $in_gallery = mysql_result( $result, $i, "in_gallery" );
        $order_number = mysql_result( $result, $i, "order_number" );
        $note = mysql_result( $result, $i, "note" );

        $note = htmlspecialchars( $note, ENT_QUOTES );

        if( $order_number > $max_order_number ) {
            $max_order_number = $order_number;
            }
        
        $checked = "";

        if( $in_gallery ) {
            $checked = "CHECKED";
            }
        
        echo "<tr>\n";
        echo "<td bgcolor=$bgColor><INPUT TYPE='text' ".
                          "MAXLENGTH=10 SIZE=4 NAME='order_number_$i' ".
                          "VALUE='$order_number'></td>\n";
        echo "<td bgcolor=$bgColor>".
            "Object ID: <b>$object_id</b>".
            "<INPUT TYPE='hidden' NAME='id_$i' VALUE='$object_id'></td>\n";
        echo "<td bgcolor=$bgColor>Price: $<INPUT TYPE='text' ".
                          "MAXLENGTH=20 SIZE=10 NAME='price_$i' ".
                          "VALUE='$price'></td>\n";
        echo "<td bgcolor=$bgColor>Gallery: <INPUT TYPE='checkbox' ".
                          "NAME='in_gallery_$i' VALUE='1' $checked></td>\n";
        echo "<td bgcolor=$bgColor>Note: <INPUT TYPE='text' ".
                          "MAXLENGTH=255 SIZE=30 NAME='note_$i' ".
                          "VALUE='$note'></td>\n";
        echo "<td bgcolor=$bgColor>[<a href='server.php?action=delete_price".
                           "&object_id=$object_id".
                           "#priceList'>delete]</td>\n";
        echo "</tr>\n\n";

        $temp = $bgColor;
        $bgColor = $altBGColor;
        $altBGColor = $temp;
        }

    $max_order_number++;
    
    
    echo "<tr>\n";
    echo "<td colspan=6>New Price:</td><tr>\n";
    echo "<tr>\n";
    echo "<td><INPUT TYPE='text' MAXLENGTH=10 SIZE=4 NAME='order_number_NEW'
             VALUE='$max_order_number'></td>\n";
    echo "<td>Object ID: <INPUT TYPE='text' MAXLENGTH=40 SIZE=20 NAME='id_NEW'
             VALUE=''></td>\n";
    echo "<td>Price: $<INPUT TYPE='text' ".
        "MAXLENGTH=20 SIZE=10 NAME='price_NEW' ".
        "VALUE=''></td>\n";
    echo "<td>Gallery: <INPUT TYPE='checkbox' ".
        "NAME='in_gallery_NEW' VALUE='1'></td>\n";
    echo "<td colspan=2>Note: <INPUT TYPE='text' ".
        "MAXLENGTH=255 SIZE=30 NAME='note_NEW' ".
        "VALUE=''></td>\n";
    echo "<td></td>\n";
    echo "</tr>\n\n";
    
    echo "</table>\n";
?>    
    <INPUT TYPE="Submit" VALUE="Update Prices">
    </FORM>
    <br>
    <br>

         
    <FORM ACTION="server.php#priceList" METHOD="post">
    <INPUT TYPE="hidden" NAME="action" VALUE="default_prices">
    <table border=1>
    <tr>
    <td><INPUT TYPE="Submit" VALUE="Restore Default Prices"></td>
    <td><INPUT TYPE="checkbox" NAME="confirm1" VALUE=1> Sure?<br>
    <INPUT TYPE="checkbox" NAME="confirm2" VALUE=1> Really sure?<td>
    </tr>
    </table>     
    </FORM>
         
<?php

         
    echo "<hr>";

    // Show auction list
    echo "Auction:<br>";
    
    $query = "SELECT object_id, start_price, start_time, ".
        "TIMESTAMPDIFF( SECOND, start_time, CURRENT_TIMESTAMP ) ".
        "   as elapsed_seconds ".
        "FROM $tableNamePrefix"."auction;";
    $result = cd_queryDatabase( $query );
    
    $numRows = mysql_numrows( $result );

    echo "<table border=1 cellpadding=5>\n";

    $bgColor = "#EEEEEE";
    $altBGColor = "#CCCCCC";
                 
    for( $i=0; $i<$numRows; $i++ ) {
        $object_id = mysql_result( $result, $i, "object_id" );
        $start_price = mysql_result( $result, $i, "start_price" );
        $start_time = mysql_result( $result, $i, "start_time" );
        $elapsed_seconds = mysql_result( $result, $i, "elapsed_seconds" );

        $price = cd_computeAuctionPrice( $start_price, $elapsed_seconds );
        
        echo "<tr>\n";
        echo "<td bgcolor=$bgColor>".
            "Object ID: <b>$object_id</b></td>\n";
        echo "<td bgcolor=$bgColor>".
            "Start Price: <b>\$$start_price</b></td>\n";
        echo "<td bgcolor=$bgColor>".
            "Start Time: <b>$start_time</b></td>\n";
        echo "<td bgcolor=$bgColor>".
            "Elapsed Seconds: <b>$elapsed_seconds</b></td>\n";
        echo "<td bgcolor=$bgColor>".
            "Current Price: <b>\$$price</b></td>\n";
        echo "</tr>\n\n";

        $temp = $bgColor;
        $bgColor = $altBGColor;
        $altBGColor = $temp;
        }
    echo "</table>\n";
    
    
    echo "<hr>";

    echo "<a href=\"server.php?action=show_log\">".
        "Show log</a>";
    echo "<hr>";
    echo "Generated for $remoteIP\n";

    /*
    echo "Name Test:<br>";

    for( $i=0; $i<100; $i++ ) {
        $character_name = cd_pickFullName();
        echo "$character_name<br>\n";
        }
    */
    }



function cd_showDetail() {
    cd_checkPassword( "show_detail" );

    $user_id = cd_getUserID();
    
    echo "[<a href=\"server.php?action=show_data" .
        "\">Main</a>]<br><br><br>";
     
    global $tableNamePrefix;

    $query = "SELECT ticket_id, email, ".
        "character_name_history, admin, blocked ".
        "FROM $tableNamePrefix"."users ".
        "WHERE user_id = '$user_id';";

    $result = cd_queryDatabase( $query );
    
    $numRows = mysql_numrows( $result );

    if( $numRows < 1 ) {
        cd_operationError( "User ID $user_id not found" );
        }
    $row = mysql_fetch_array( $result, MYSQL_ASSOC );

    $ticket_id = $row[ "ticket_id" ];
    $character_name_history = $row[ "character_name_history" ];
    $admin = $row[ "admin" ];
    $blocked = $row[ "blocked" ];
    $email = $row[ "email" ];


    echo "User ID: $user_id<br>\n";
    echo "Ticket: $ticket_id<br>\n";
    echo "Email: $email<br>\n";

    $blockedChecked = "";
    if( $blocked ) {
        $blockedChecked = "checked";
        }
    $adminChecked = "";
    if( $admin ) {
        $adminChecked = "checked";
        }
?>
            <FORM ACTION="server.php" METHOD="post">
    <INPUT TYPE="hidden" NAME="action" VALUE="update_user">
    <INPUT TYPE="hidden" NAME="user_id" VALUE="<?php echo $user_id;?>">
    Blocked <INPUT TYPE="checkbox" NAME="blocked" VALUE=1
                 <?php echo $blockedChecked;?> ><br>
    Admin <INPUT TYPE="checkbox" NAME="admin" VALUE=1
                 <?php echo $adminChecked;?> ><br>
    <INPUT TYPE="Submit" VALUE="Update">
<?php

    echo "<br><br>Name history:<br>";

    $names = preg_split( "/\s+/", $character_name_history );
    
    foreach( $names as $name ) {
        echo "$name<br>";
        }
    
    }




function cd_blockUserID() {
    cd_checkPassword( "block_user_id" );


    global $tableNamePrefix;
        
    $user_id = cd_getUserID();

    $blocked = cd_requestFilter( "blocked", "/[01]/" );

    // don't touch admin
    if( cd_updateUser_internal( $user_id, $blocked, -1 ) ) {
        cd_showData();
        }
    }




function cd_updateUser() {
    cd_checkPassword( "update_user" );


    $user_id = cd_getUserID();

    $blocked = cd_requestFilter( "blocked", "/[1]/", "0" );
    $admin = cd_requestFilter( "admin", "/[1]/", "0" );

    if( cd_updateUser_internal( $user_id, $blocked, $admin ) ) {
        cd_showDetail();
        }
    }


// set either to -1 to leave unchanged
// returns 1 on success
function cd_updateUser_internal( $user_id, $blocked, $admin ) {
    
    global $tableNamePrefix;
        
    
    global $remoteIP;
    

    
    $query = "SELECT user_id, blocked, admin FROM $tableNamePrefix"."users ".
        "WHERE user_id = '$user_id';";
    $result = cd_queryDatabase( $query );

    $numRows = mysql_numrows( $result );

    if( $numRows == 1 ) {
        $old_blocked = mysql_result( $result, 0, "blocked" );
        $old_admin = mysql_result( $result, 0, "admin" );

        if( $admin == -1 ) {
            $admin = $old_admin;
            }
        if( $blocked == -1 ) {
            $blocked = $old_blocked;
            }
        
        
        $query = "UPDATE $tableNamePrefix"."users SET " .
            "blocked = '$blocked', admin = '$admin' " .
            "WHERE user_id = '$user_id';";
        
        $result = cd_queryDatabase( $query );

                
        $query = "UPDATE $tableNamePrefix"."houses SET " .
            "blocked = '$blocked' " .
            "WHERE user_id = '$user_id';";
        
        $result = cd_queryDatabase( $query );


        if( $old_admin != $admin ) {
            cd_log( "$user_id admin changed to $admin by $remoteIP" );
            }
        if( $old_blocked != $blocked ) {
            cd_log( "$user_id blocked changed to $blocked by $remoteIP" );
            }
        
        return 1;
        }
    else {
        cd_log( "$user_id not found for $remoteIP" );

        echo "$user_id not found";
        }
    return 0;
    }




function cd_defaultPrices() {
    cd_checkPassword( "default_prices" );


    global $tableNamePrefix;
    global $remoteIP;

    $confirm1 = cd_requestFilter( "confirm1", "/[1]/" );
    $confirm2 = cd_requestFilter( "confirm2", "/[1]/" );
    
    if( $confirm1 == 1 && $confirm2 == 1 ) {

        cd_restoreDefaultPrices();

        cd_log( "Default prices restored by $remoteIP" );
    
        cd_showPrices();
        }
    else {
        cd_nonFatalError( "Double confirmation boxes not checked to restore ".
                          "default prices" );
        }
    }






function cd_updatePrices() {
    cd_checkPassword( "update_prices" );


    global $tableNamePrefix;
    global $remoteIP;

    
    
    $num_prices = cd_requestFilter( "num_prices", "/\d+/" );

    
    if( $num_prices > 0 ) {
        

        for( $i=0; $i<$num_prices; $i++ ) {
            $id = cd_requestFilter( "id_$i", "/\d+/" );
            $price = cd_requestFilter( "price_$i", "/\d+/" );
            $in_gallery = cd_requestFilter( "in_gallery_$i", "/1/", "0" );
            $order_number = cd_requestFilter( "order_number_$i", "/\d+/" );
            
            $note = cd_requestFilter( "note_$i", "/[A-Z0-9.' _\-,!?()]+/i" );
            
            if( $id != "" && $price != "" && $order_number != "" ) {

                // note may have ' in it
                $note = mysql_real_escape_string( $note );
                
                $query = "UPDATE $tableNamePrefix"."prices SET " .
                    "price = '$price', in_gallery = '$in_gallery', ".
                    "order_number = '$order_number', note = '$note' " .
                    "WHERE object_id = '$id';";
                
                $result = cd_queryDatabase( $query );
                }
            }
        
        cd_log( "Prices updated by $remoteIP" );
        }

    // new one to insert?

    $id = cd_requestFilter( "id_NEW", "/\d+/" );

    $price = cd_requestFilter( "price_NEW", "/\d+/" );

    $in_gallery = cd_requestFilter( "in_gallery_NEW", "/1/", "0" );

    $order_number = cd_requestFilter( "order_number_NEW", "/\d+/" );
    
    $note = cd_requestFilter( "note_NEW", "/[A-Z0-9.' _-]+/i" );

    
    if( $id != "" && $price != "" && $order_number != "" ) {
        // first, make sure it doesn't already exist
        $query = "SELECT COUNT(object_id) FROM $tableNamePrefix"."prices ".
            "WHERE object_id = '$id';";
        $result = cd_queryDatabase( $query );

        $count = mysql_result( $result, 0, 0 );
        if( $count != 0 ) {

            cd_nonFatalError( "Price already exists for '$id'" );            
            }

        // note may have ' in it
        $note = mysql_real_escape_string( $note );
        
        
        $query = "INSERT INTO $tableNamePrefix"."prices VALUES ( " .
            "'$id', '$price', '$in_gallery', '$order_number', '$note' );";
        $result = cd_queryDatabase( $query );

        if( $result ) {
            $galleryLabel = "";
            if( $in_gallery ) {
                $galleryLabel = "gallery ";
                }
            
            cd_log( "New $galleryLabel"."price ($id, \$$price, '$note' ) ".
                    "created by $remoteIP" );
            }

        if( $in_gallery == 1 ) {
            cd_startAuction( $id, $order_number, $price );
            }
        
        }
    


    
    cd_showPrices();
    }




function cd_deletePrice() {
    cd_checkPassword( "delete_price" );


    global $tableNamePrefix;
    global $remoteIP;


    $success = false;
    
    $object_id = cd_requestFilter( "object_id", "/\d+/" );
    if( $object_id != "" ) {

        $query = "DELETE FROM $tableNamePrefix"."prices " .
            "WHERE object_id = '$object_id';";
        
        $result = cd_queryDatabase( $query );

        if( $result && mysql_affected_rows() == 1 ) {
            cd_log( "Price for $object_id deleted by $remoteIP" );
            $success = true;
            }
        }

    if( ! $success ) {
        cd_nonFatalError( "Failed to delete price for '$object_id'" );
        }
    
    cd_showPrices();
    }





$cd_mysqlLink;




// general-purpose functions down here, many copied from seedBlogs

/**
 * Connects to the database according to the database variables.
 */  
function cd_connectToDatabase() {
    global $databaseServer,
        $databaseUsername, $databasePassword, $databaseName,
        $cd_mysqlLink;
    
    
    $cd_mysqlLink =
        mysql_connect( $databaseServer, $databaseUsername, $databasePassword )
        or cd_operationError( "Could not connect to database server: " .
                              mysql_error() );
    
    mysql_select_db( $databaseName )
        or cd_operationError( "Could not select $databaseName database: " .
                              mysql_error() );
    }


 
/**
 * Closes the database connection.
 */
function cd_closeDatabase() {
    global $cd_mysqlLink;
    
    mysql_close( $cd_mysqlLink );
    }



/**
 * Queries the database, and dies with an error message on failure.
 *
 * @param $inQueryString the SQL query string.
 *
 * @return a result handle that can be passed to other mysql functions.
 */
function cd_queryDatabase( $inQueryString ) {
    global $cd_mysqlLink;

    if( gettype( $cd_mysqlLink ) != "resource" ) {
        // not a valid mysql link?
        cd_connectToDatabase();
        }
    
    $result = mysql_query( $inQueryString, $cd_mysqlLink );
    
    if( $result == FALSE ) {

        $errorNumber = mysql_errno();

        // server lost or gone?
        if( $errorNumber == 2006 ||
            $errorNumber == 2013 ||
            // access denied?
            $errorNumber == 1044 ||
            $errorNumber == 1045 ||
            // no db selected?
            $errorNumber == 1046 ) {

            // connect again?
            cd_closeDatabase();
            cd_connectToDatabase();

            $result = mysql_query( $inQueryString, $cd_mysqlLink )
                or cd_operationError(
                    "Database query failed:<BR>$inQueryString<BR><BR>" .
                    mysql_error() );
            }
        else {
            // some other error (we're still connected, so we can
            // add log messages to database
            cd_fatalError( "Database query failed:<BR>$inQueryString<BR><BR>" .
                           mysql_error() );
            }
        }
    

    return $result;
    }



/**
 * Gets the CURRENT_TIMESTAMP string from MySQL database.
 */
function cd_getMySQLTimestamp() {
    $result = cd_queryDatabase( "SELECT CURRENT_TIMESTAMP;" );
    return mysql_result( $result, 0, "CURRENT_TIMESTAMP" );
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

    if( $enableLog ) {
        $user_id = cd_getUserID();
        
        if( $user_id != "" ) {
            $message = "[user_id = $user_id] " . $message;
            }

        $slashedMessage = mysql_real_escape_string( $message );
        
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
 * Displays the non-fatal error page and dies.
 *
 * @param $message the error message to display on the error page.
 */
function cd_nonFatalError( $message ) {

    cd_checkPassword( "nonFatalError" );
    
     echo "[<a href=\"server.php?action=show_data" .
         "\">Main</a>]<br><br><br>";
    
    // for now, just print error message
    $logMessage = "Error:  $message";
    
    echo( $logMessage );

    cd_log( $logMessage );
    
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




/**
 * Filters a $_REQUEST variable using a regex match.
 *
 * Returns "" (or specified default value) if there is no match.
 */
function cd_requestFilter( $inRequestVariable, $inRegex, $inDefault = "" ) {
    if( ! isset( $_REQUEST[ $inRequestVariable ] ) ) {
        return $inDefault;
        }
    
    $numMatches = preg_match( $inRegex,
                              $_REQUEST[ $inRequestVariable ], $matches );

    if( $numMatches != 1 ) {
        return $inDefault;
        }
        
    return $matches[0];
    }





// this function checks the password directly from a request variable
// or via hash from a cookie.
//
// It then sets a new cookie for the next request.
//
// This avoids storing the password itself in the cookie, so a stale cookie
// (cached by a browser) can't be used to figure out the password and log in
// later. 
function cd_checkPassword( $inFunctionName ) {
    $password = "";
    $password_hash = "";

    $badCookie = false;
    
    
    global $accessPasswords, $tableNamePrefix, $remoteIP, $enableYubikey,
        $passwordHashingPepper;

    $cookieName = $tableNamePrefix . "cookie_password_hash";

    
    $passwordSent = false;
    
    if( isset( $_REQUEST[ "password" ] ) ) {
        $passwordSent = true;
        
        $password = cd_hmac_sha1( $passwordHashingPepper,
                                  $_REQUEST[ "password" ] );

        // generate a new hash cookie from this password
        $newSalt = time();
        $newHash = md5( $newSalt . $password );
        
        $password_hash = $newSalt . "_" . $newHash;
        }
    else if( isset( $_COOKIE[ $cookieName ] ) ) {
        $password_hash = $_COOKIE[ $cookieName ];
        
        // check that it's a good hash
        
        $hashParts = preg_split( "/_/", $password_hash );

        // default, to show in log message on failure
        // gets replaced if cookie contains a good hash
        $password = "(bad cookie:  $password_hash)";

        $badCookie = true;
        
        if( count( $hashParts ) == 2 ) {
            
            $salt = $hashParts[0];
            $hash = $hashParts[1];

            foreach( $accessPasswords as $truePassword ) {    
                $trueHash = md5( $salt . $truePassword );
            
                if( $trueHash == $hash ) {
                    $password = $truePassword;
                    $badCookie = false;
                    }
                }
            
            }
        }
    else {
        // no request variable, no cookie
        // cookie probably expired
        $badCookie = true;
        $password_hash = "(no cookie.  expired?)";
        }
    
        
    
    if( ! in_array( $password, $accessPasswords ) ) {

        if( ! $badCookie ) {
            
            echo "Incorrect password.";

            cd_log( "Failed $inFunctionName access with password:  ".
                    "$password" );
            }
        else {
            echo "Session expired.";
                
            cd_log( "Failed $inFunctionName access with bad cookie:  ".
                    "$password_hash" );
            }
        
        die();
        }
    else {
        
        if( $passwordSent && $enableYubikey ) {
            global $yubikeyIDs, $yubicoClientID, $yubicoSecretKey,
                $serverSecretKey;
            
            $yubikey = $_REQUEST[ "yubikey" ];

            $index = array_search( $password, $accessPasswords );
            $yubikeyIDList = preg_split( "/:/", $yubikeyIDs[ $index ] );

            $providedID = substr( $yubikey, 0, 12 );

            if( ! in_array( $providedID, $yubikeyIDList ) ) {
                echo "Provided Yubikey does not match ID for this password.";
                die();
                }
            
            
            $nonce = cd_hmac_sha1( $serverSecretKey, uniqid() );
            
            $callURL =
                "http://api2.yubico.com/wsapi/2.0/verify?id=$yubicoClientID".
                "&otp=$yubikey&nonce=$nonce";
            
            $result = trim( file_get_contents( $callURL ) );

            $resultLines = preg_split( "/\s+/", $result );

            sort( $resultLines );

            $resultPairs = array();

            $messageToSignParts = array();
            
            foreach( $resultLines as $line ) {
                // careful here, because = is used in base-64 encoding
                // replace first = in a line (the key/value separator)
                // with #
                
                $lineToParse = preg_replace( '/=/', '#', $line, 1 );

                // now split on # instead of =
                $parts = preg_split( "/#/", $lineToParse );

                $resultPairs[$parts[0]] = $parts[1];

                if( $parts[0] != "h" ) {
                    // include all but signature in message to sign
                    $messageToSignParts[] = $line;
                    }
                }
            $messageToSign = implode( "&", $messageToSignParts );

            $trueSig =
                base64_encode(
                    hash_hmac( 'sha1',
                               $messageToSign,
                               // need to pass in raw key
                               base64_decode( $yubicoSecretKey ),
                               true) );
            
            if( $trueSig != $resultPairs["h"] ) {
                echo "Yubikey authentication failed.<br>";
                echo "Bad signature from authentication server<br>";
                die();
                }

            $status = $resultPairs["status"];
            if( $status != "OK" ) {
                echo "Yubikey authentication failed: $status";
                die();
                }

            }
        
        // set cookie again, renewing it, expires in 24 hours
        $expireTime = time() + 60 * 60 * 24;
    
        setcookie( $cookieName, $password_hash, $expireTime, "/" );
        }
    }
 



function cd_clearPasswordCookie() {
    global $tableNamePrefix;

    $cookieName = $tableNamePrefix . "cookie_password_hash";

    // expire 24 hours ago (to avoid timezone issues)
    $expireTime = time() - 60 * 60 * 24;

    setcookie( $cookieName, "", $expireTime, "/" );
    }




function cd_getTotalSpace() {
    global $tableNamePrefix;

    $query = "SELECT SUM( DATA_LENGTH ) ".
        "FROM information_schema.tables ".
        "WHERE TABLE_NAME like '$tableNamePrefix%';";

    $result = cd_queryDatabase( $query );

    return mysql_result( $result, 0, 0 );
    }


function cd_countUsers() {
    global $tableNamePrefix;

    $query = "SELECT COUNT(*) ".
        "FROM $tableNamePrefix"."houses;";
    $result = cd_queryDatabase( $query );

    return mysql_result( $result, 0, 0 );
    }




function cd_hmac_sha1( $inKey, $inData ) {
    return hash_hmac( "sha1", 
                      $inData, $inKey );
    }





// encodes a string of 0s and 1s into an ASCII readable-base32 string 
function cd_readableBase32EncodeFromBitString( $inBitString ) {
    global $readableBase32DigitArray;


    // chunks of 5 bits
    $chunksOfFive = str_split( $inBitString, 5 );

    $encodedString = "";
    foreach( $chunksOfFive as $chunk ) {
        $index = bindec( $chunk );

        $encodedString = $encodedString . $readableBase32DigitArray[ $index ];
        }
    
    return $encodedString;
    }
 


// decodes an ASCII readable-base32 string into a string of 0s and 1s 
function cd_readableBase32DecodeToBitString( $inBase32String ) {
    global $readableBase32DigitArray;
    
    $digits = str_split( $inBase32String );

    $bitString = "";

    foreach( $digits as $digit ) {
        $index = array_search( $digit, $readableBase32DigitArray );

        $binDigitString = decbin( $index );

        // pad with 0s
        $binDigitString =
            substr( "00000", 0, 5 - strlen( $binDigitString ) ) .
            $binDigitString;

        $bitString = $bitString . $binDigitString;
        }

    return $bitString;
    }
 
 
 
// decodes a ASCII hex string into an array of 0s and 1s 
function cd_hexDecodeToBitString( $inHexString ) {
        global $readableBase32DigitArray;
    
    $digits = str_split( $inHexString );

    $bitString = "";

    foreach( $digits as $digit ) {
        $index = hexdec( $digit );

        $binDigitString = decbin( $index );

        // pad with 0s
        $binDigitString =
            substr( "0000", 0, 4 - strlen( $binDigitString ) ) .
            $binDigitString;

        $bitString = $bitString . $binDigitString;
        }

    return $bitString;
    }





?>
