<?php

// Basic settings
// You must set these for the server to work
// AND these must be shared by the ticketServer operating in the same
// database (because tickets are used to uniquely identify users).

$databaseServer = "localhost";
$databaseUsername = "testUser";
$databasePassword = "testPassword";
$databaseName = "test";

// The URL of to the server.php script.
$fullServerURL = "http://localhost/jcr13/castleServer/server.php";

// The URL of the ticket server's server.php script.
// This is used to fetch assigned ticket IDs for users.
$ticketServerURL = "http://localhost/jcr13/ticketServer/server.php";


// The ticket server encrypts the ticket IDs that it returns to us
// using this shared secret
$sharedEncryptionSecret = "19fbc6168268d7a80945e35d999f0d0ddae4cdff";



// The URL of the main, public-face website
$mainSiteURL = "http://FIXME";


// used by server for signing price lists to verify
// that prices are valid later, and for other things that need to be
// uniquely generated by this server
$serverSecretKey = "0aa02f4b4fb72740bf927ecdc94fffd21506a3a3";



// End Basic settings



// Customization settings

// Adjust these to change the way the server  works.


// Prefix to use in table names (in case more than one application is using
// the same database).
$tableNamePrefix = "castleServer_";





$enableLog = 1;


// should web-based admin require yubikey two-factor authentication?
$enableYubikey = 1;

// 12-character Yubikey IDs, one list for each access password
// each list is a set of ids separated by :
// (there can be more than one Yubikey ID associated with each password)
$yubikeyIDs = array( "ccccccbjlfbi:ccccccbjnhjc:ccccccbjnhjn", "ccccccbjlfbi" );

// used for verifying response that comes back from yubico
// Note that these are working values, but because they are in a public
// repository, they are not secret and should be replaced with your own
// values (go here:  https://upgrade.yubico.com/getapikey/ )
$yubicoClientID = "9943";
$yubicoSecretKey = "rcGgz0rca1gqqsa/GDMwXFAHjWw=";


// For hashing admin passwords so that they don't appear in the clear
// in this file.
// You can change this to your own string so that password hashes in
// this file differ from hashes of the same passwords used elsewhere.
$passwordHashingPepper = "8ea60c7ea2b695a006205f5c133603bb155d85d4";

// passwords are given as hashes below, computed by:
// hmac_sha1( $passwordHashingPepper, $password )
// Where $passwordHashingPepper is used as the hmac key.

// For convenience, after setting a $passwordHashingPepper and chosing a
// password, hashes can be generated by invoking passwordHashUtility.php
// in your browser.

// default passwords that have been included as hashes below are:
// "secret" and "secret2"

// hashes of passwords for for web-based admin access
$accessPasswords = array( "6616d4211911cc5aa4d30adcf5af54c2814b6508",
                          "806a72a0c70240c99d36a38b1164fe8c7fdeda71" );



// Default behavior is NOT to depend on a cron job to call check_for_flush
// periodically.  With default config, some unlucky client call triggers the
// flush and waits for it to finish before that client is served.
// Better behavior can be had by disabling this and manually calling
// check_for_flush with cron.
// (Flush removes stale house checkouts, etc.)
$flushDuringClientCalls = true;



// Default behavior is NOT to depend on a compiled, headless client for
// robbery simulation (cheat-blocking).  If you leave this disabled, many
// cheats (walking through walls, etc.) will be allowed through.  You can
// still manually detect these by watching security tapes.
$checkRobberiesWithHeadlessClient = true;

// If enabled, list the ports that headless clients are running on here.
// Server will pick one at random for each request (or try other ones
// after one fails).
$headlessClientPorts = array( 5077, 5078 );




$emailAdminOnFatalError = 0;

$adminEmail = "jason@server.com";



// mail settings

$siteEmailAddress = "Jason Rohrer <jcr13@cornell.edu>";

// if off, then raw sendmail is used instead 
$useSMTP = 0;

// SMTP requires that the PEAR Mail package is installed
// set the include path here for Mail.php, if needed:
/*
ini_set( 'include_path',
         ini_get( 'include_path' ) . PATH_SEPARATOR . '/home/jcr13/php' );
*/

$smtpHost = "ssl://mail.server.com";

$smtpPort = "465";

$smtpUsername = "jason@server.com";

$smtpPassword = "secret";







// prices that are loaded when database is first setup
// they can be changed manually later from the admin page
//
// array( ID, Price, InGallery, Note )
$defaultPrices = array(
    array( 1, 10, 0, "Wooden Wall" ),
    array( 2, 30, 0, "Steel Wall" ),
    array( 3, 50, 0, "Concrete Wall" ),
    array( 0, 0, 0, "Empty Floor" ),
    array( 21, 20, 0, "Door" ),
    array( 20, 15, 0, "Window" ),
    array( 111, 100, 0, "Pit" ),
    array( 103, 200, 0, "Power Source" ),
    array( 102, 5, 0, "Wiring" ),
    array( 120, 5, 0, "Vertical Wiring" ),
    array( 121, 5, 0, "Horizontal Wiring" ),
    array( 101, 50, 0, "Pressure Toggle Switch (Starts Off)" ),
    array( 108, 50, 0, "Pressure Toggle Switch (Starts On)" ),
    array( 100, 50, 0, "Sticking Pressure Switch" ),
    array( 107, 50, 0, "Rotary Toggle Switch" ),
    array( 109, 10, 0, "Indicator Light (Conducting)" ),
    array( 113, 10, 0, "Indicator Light (Non-Conducting)" ),
    array( 106, 10, 0, "Wire Bridge" ),
    array( 104, 20, 0, "Voltage-triggered Switch" ),
    array( 105, 20, 0, "Voltage-triggered Inverted Switch" ),
    array( 30, 100, 0, "Automatic Door" ),
    array( 110, 50, 0, "Electric Floor" ),
    array( 112, 200, 0, "Powered Trapdoor" ),
    array( 70, 200, 0, "Pit Bull" ),
    array( 71, 100, 0, "Chihuahua" ),
    array( 72, 20, 0, "Cat" ),
    
    array( 500, 200, 0, "Saw" ),
    array( 509, 200, 0, "Crowbar" ),
    array( 501, 600, 0, "Cutting Torch" ),
    array( 502, 2000, 0, "S-W Model 36" ),
    array( 503, 4, 0, "Brick" ),
    array( 504, 20, 0, "Voltage Detector" ),
    array( 505, 12, 0, "Doorstop" ),
    array( 506, 200, 0, "Wire Cutters" ),
    array( 507, 1000, 0, "Ammonium Nitrate Diesel Bomb" ),
    array( 508, 40, 0, "Drugged Meat" ),
    array( 510, 4, 0, "Water" ),
    array( 511, 1200, 0, "Ladder" ),

    array( 2000, 100000, 1, "Owl - Rohrer" ),
    array( 2001, 100000, 1, "Sky - Rohrer" ),
    array( 2002, 100000, 1, "Everything I Love --- by Stephen Lavelle" ),
    array( 2003, 100000, 1, "Breeze --- by Jenova Chen" ),
    array( 2004, 100000, 1, "Kiwi! --- by Danny Ledonne" ),
    array( 2005, 100000, 1, "Are you embarrassed to dance? --- by Ashly Burch" ),
    array( 2006, 100000, 1, "Lips for Eyelids --- by Ashly Burch" ),
    array( 2007, 100000, 1, "Escape Found --- by Scott Brodie" ),
    array( 2008, 100000, 1, "Old Guard --- by Scott Brodie" ),
    array( 2009, 100000, 1, "Bound Sun --- by Scott Brodie" ),
    array( 2010, 100000, 1, "Eyes Wide Spread --- by Daniel Cook" ),
    array( 2011, 100000, 1, "Down Goes Frazier --- by Jason Stevenson" ),
    array( 2012, 100000, 1, "Yellow Bars --- by Jason Stevenson" ),
    array( 2013, 100000, 1, "Obviously, a major malfunction --- by Jason Stevenson" ),
    array( 2014, 100000, 1, "Flame Out --- by Jason Stevenson" ),
    array( 2015, 100000, 1, "Band of Brothers --- by Jason Stevenson" ),
    array( 2016, 100000, 1, "Mike Shot --- by Jason Stevenson" ),
    array( 2017, 100000, 1, "Tentacles! --- by Jason Stevenson" ),
    array( 2018, 100000, 1, "Zapruder260 --- by Jason Stevenson" ),
    array( 2019, 100000, 1, "Delicate Arch --- by Jason Stevenson" ),
    array( 2020, 100000, 1, "Manhattan --- by William Beebe" ),
    array( 2021, 100000, 1, "Core --- by William Beebe" ),
    array( 2022, 100000, 1, "Night, Harlem --- by William Beebe" ),
    array( 2023, 100000, 1, "Night City --- by William Beebe" ),
    array( 2024, 100000, 1, "Lulu --- by American McGee" ),
    array( 2025, 100000, 1, "Crossed --- by American McGee" ),
    array( 2026, 100000, 1, "Flicker --- by American McGee" ),
    array( 2027, 100000, 1, "Xian --- by American McGee" ),
    array( 2028, 100000, 1, "Whoops --- by Anthony Burch" ),
    array( 2029, 100000, 1, "Today --- by Nick Montfort" ),
    array( 2030, 100000, 1, "Felicity --- by Cactus" ),
    array( 2031, 100000, 1, "Checker --- by Chris Hecker" ),
    array( 2032, 100000, 1, "Warhol --- by Victor Stone" ),
    array( 2033, 100000, 1, "Ass Over Tea Kettle --- by Victor Stone" ),
    array( 2034, 100000, 1, "Study in Blue --- by Rod Humble" ),
    array( 2035, 100000, 1, "Portrait of a Raindrop --- by Rod Humble" ),
    array( 2036, 100000, 1, "The Storm --- by Rod Humble" ),
    array( 2037, 100000, 1, "The Escape --- by Rod Humble" ),

    array( 2038, 100000, 1, "Skull --- by David S. Goyer" ),
    array( 2039, 100000, 1, "Crow in Red --- by Adam Saltsman" ),
    array( 2040, 100000, 1, "Geovisage One --- by Adam Saltsman" ),
    array( 2041, 100000, 1, "Geovisage Two --- by Adam Saltsman" ),
    array( 2042, 100000, 1, "Geovisage Three --- by Adam Saltsman" ),
    array( 2043, 100000, 1, "Savanna --- by Adam Saltsman" ),
    array( 2044, 100000, 1, "Sentinel --- by Adam Saltsman" ),
    array( 2045, 100000, 1, "The Grim Bulldog --- by Jason Stevenson" ),
    array( 2046, 100000, 1, "Wilderness --- by Terry Cavanagh" ),
    array( 2047, 100000, 1, "Electric Slide --- by Darius Kazemi" ),
    array( 2048, 100000, 1, "Rhizome --- by Darius Kazemi" ),
    array( 2049, 100000, 1, "Stay Up All Night --- by Darius Kazemi" ),
    array( 2050, 100000, 1, "Metaphysicians --- by Darius Kazemi" ),
    array( 2051, 100000, 1, "Summer Reach --- by William Beebe" ),
    array( 2052, 100000, 1, "Toby --- by Jordan Magnuson" ),
    array( 2053, 100000, 1, "RYB 1 --- by Alex Diamond" ),
    array( 2054, 100000, 1, "RYB 2 --- by Alex Diamond" ),
    array( 2055, 100000, 1, "RYB 3 --- by Alex Diamond" ),
    array( 2056, 100000, 1, "RYB 4 --- by Alex Diamond" ),
    array( 2057, 100000, 1, "RYB 5 --- by Alex Diamond" ),
    array( 2058, 100000, 1, "Dermatillomanian Handle --- by Alex Diamond" ),
    array( 2059, 100000, 1, "Dermatillomanian Cultivation --- by Alex Diamond" ),
    array( 2060, 100000, 1, "Dermatillomanian Sheath --- by Alex Diamond" ),
    array( 2061, 100000, 1, "Dermatillomanian Seduction --- by Alex Diamond" ),
    array( 2062, 100000, 1, "Dermatillomanian Abyss --- by Alex Diamond" ),
    array( 2063, 100000, 1, "UKU1NbyLyNCsRgVSsoVOLosPEOiyGRJKRdUh2a1WP5P6" ),
    array( 2064, 100000, 1, "i1wZebCgqGYLcBWK7K888dfPdJs57ohaoF35NBSp8xxh" ),
    array( 2065, 100000, 1, "sKZAkUoDPjKdEmOaucx1RUqHi444FV3JquwzIVLFBI8B" ),
    array( 2066, 100000, 1, "H8d9k15aVQob0d4pXEiFGL6Ry2HMRckCdKD5MJT1sCh9" ),
    array( 2067, 100000, 1, "Hrn6kGh64jqCISghEEyyU13D4EV5gWItFD0VaJSKBEWZ" ),
    array( 2068, 100000, 1, "Dogs --- by Kyle Pulver" ),
    array( 2069, 100000, 1, "Set --- by Kyle Pulver" ),
    array( 2070, 100000, 1, "Rise --- by Kyle Pulver" ),
    array( 2071, 100000, 1, "Haze --- by Kyle Pulver" ),
    array( 2072, 100000, 1, "Four --- by Kyle Pulver" ),
    array( 2073, 100000, 1, "Prism --- by Kyle Pulver" ),
    array( 2074, 100000, 1, "Protect Me from What I Want --- by Nick Montfort" ),
    array( 2075, 100000, 1, "Forward --- by Jason Stevenson" ),
    array( 2076, 100000, 1, "Skull over Red --- by Mark Johns" ),
    array( 2077, 100000, 1, "Dead End --- by S. Galvin" ),
    array( 2078, 100000, 1, "Ceci n'est pas une beetle --- by S. Galvin" ),
    array( 2079, 100000, 1, "Palomar --- by S. Galvin" ),
    array( 2080, 100000, 1, "Distant Chrome --- by S. Galvin" ),
    array( 2081, 100000, 1, "Momento Mori --- by Nova Jiang" ),
    array( 2082, 100000, 1, "Inspiration --- by Jasper Byrne" ),
    array( 2083, 100000, 1, "Church --- by Lauren Serafin" ),
    array( 2084, 100000, 1, "Evolve --- by Lauren Serafin" ),
    array( 2085, 100000, 1, "Friends --- by Lauren Serafin" ),
    array( 2086, 100000, 1, "Music --- by Lauren Serafin" ),
    array( 2087, 100000, 1, "School --- by Lauren Serafin" ),
    array( 2088, 100000, 1, "Isolation --- by Lauren Serafin" ),
    array( 2089, 100000, 1, "Jacob III de Gheyn --- by Cloobrandt" ),
    array( 2090, 100000, 1, "Sarsaparilla Day --- by Art Vogt" ),
    array( 2091, 100000, 1, "Sarsaparilla Night --- by Art Vogt" ),
    array( 2092, 100000, 1, "Sarsaparilla Sunset --- by Art Vogt" ),
    array( 2093, 100000, 1, "Ristretto --- by Andy Lin" ),
    array( 2094, 100000, 1, "Blythe --- by Andy Lin" ),
    array( 2095, 100000, 1, "Jane --- by Andy Lin" ),
    array( 2096, 100000, 1, "Full --- by Mark Essen" ),
    array( 2097, 100000, 1, "Empty --- by Mark Essen" ),
    array( 2098, 100000, 1, "Doing Time --- by Charlie Franco" ),
    array( 2099, 100000, 1, "Knock Out --- by Charlie Franco" ),
    array( 2100, 100000, 1, "Snow Mask --- by Charlie Franco" ),
    array( 2101, 100000, 1, "Stars-N-Stripes --- by Charlie Franco" ),
    array( 2102, 100000, 1, "Tongue Out --- by Charlie Franco" ),
    array( 2103, 100000, 1, "Sierra --- by William Beebe" ),
    array( 2104, 100000, 1, "Fog --- by William Beebe" ),
    array( 2105, 100000, 1, "Aegis --- by William Beebe" ),
    array( 2106, 100000, 1, "Weave --- by William Beebe" ),
    array( 2107, 100000, 1, "Brooklyn --- by William Beebe" ),
    array( 2108, 100000, 1, "Sauron --- by William Beebe" ),
    array( 2109, 100000, 1, "Color Field --- by William Beebe" ),
    array( 2110, 100000, 1, "Counterpoint --- by William Beebe" ),
    array( 2111, 100000, 1, "Future Buildings --- by William Beebe" ),
    array( 2112, 100000, 1, "Shield --- by William Beebe" ),
    array( 2113, 100000, 1, "Domicile --- by William Beebe" ),
    array( 2114, 100000, 1, "Cityscape --- by William Beebe" ),
    array( 2115, 100000, 1, "Form --- by William Beebe" ),
    array( 2116, 100000, 1, "Medusa's Head --- by William Beebe" ),
    array( 2117, 100000, 1, "A Snail in the Sun --- by Jason and Willow Adaska" ),

    array( 2118, 100000, 1, "Burning of Parliment --- by Frank Lantz" ),
    array( 2119, 100000, 1, "Here is a Hand.png --- by Frank Lantz" ),
    array( 2120, 100000, 1, "Hexagram Orange --- by Frank Lantz" ),
    array( 2121, 100000, 1, "Jason --- by Frank Lantz" ),
    array( 2122, 100000, 1, "Ko --- by Frank Lantz" ),
    array( 2123, 100000, 1, "Speech Balloon --- by Frank Lantz" ),
    array( 2124, 100000, 1, "Keep --- by Richard Lemarchand" ),
    array( 2125, 100000, 1, "The Knowledge of Good and Evil --- by Richard Lemarchand" ),
    array( 2126, 100000, 1, "Disintegration --- by Andy Nealen" ),
    array( 2127, 100000, 1, "Membrane --- by Andy Nealen" ),
    array( 2128, 100000, 1, "Aero --- by Andy Nealen" ),
    array( 2129, 100000, 1, "Blio --- by Andy Nealen" ),
    array( 2130, 100000, 1, "Cono --- by Andy Nealen" ),
    array( 2131, 100000, 1, "Alien --- by Matthew Diamond" ),
    array( 2132, 100000, 1, "Infected --- by Matthew Diamond" ),
    array( 2133, 100000, 1, "Surge After Hokusai --- by Steven Diamond" ),
    array( 2134, 100000, 1, "Storm Center --- by Steven Diamond" ),
    array( 2135, 100000, 1, "Having a Read --- by Mike Treanor" ),
    array( 2136, 100000, 1, "Beast --- by Ashley Davis" ),
    array( 2137, 100000, 1, "Mountain --- by Samuel Roberts" ),
    array( 2138, 100000, 1, "Happy Tiger --- by Olivier Lejade" ),
    array( 2139, 100000, 1, "Eiffel at Dawn --- by Olivier Lejade" ),
    array( 2140, 100000, 1, "Girls, Let's Start a Riot! --- by Moboid" ),
    array( 2141, 100000, 1, "Real Art! --- by Edmund McMillen" ),
    array( 2142, 100000, 1, "One of Two --- by Chris Bell" ),
    array( 2143, 100000, 1, "Sky --- by Jason Rhorer" ),
    array( 2144, 100000, 1, "Mouth --- by Phil Fish" ),
    array( 2145, 100000, 1, "Redface --- by Phil Fish" ),
    array( 2146, 100000, 1, "The Watermelon --- by Derek Yu" ),
    array( 2147, 100000, 1, "Woman by the Sea --- by Daniel Benmergui" ),
    array( 2148, 100000, 1, "Woman by the Sea --- by Daniel Benmergui" ),
    array( 2149, 100000, 1, "Attack of the Clone Maze --- by Ayza" ),
    array( 2150, 100000, 1, "Run From Blue --- by Ayza" ),
    array( 2151, 100000, 1, "Watch Out, There's Traps Set --- by Ayza" ),
    array( 2152, 100000, 1, "Goth Knight Huskarl --- by Mez" ),
    array( 2153, 100000, 1, "Roman Knight Order Giver --- by Mez" ),
    array( 2154, 100000, 1, "Pepper --- by Jackie Rohrer" )
    );

    
// price change factor when reselling items
$resaleRate = 0.5;


// list of all mobile objects (those that don't block family exit)
$mobileList = array( 70, 71, 72 );

// list of all possible objects to use as wife
$wifeList = array( 1010,
                   1011,
                   1012,
                   1013 );
// same for sons
$sonList = array( 1020,
                  1021,
                  1022,
                  1023 );

// same for daughters
$daughterList = array( 1040,
                       1041,
                       1042,
                       1043 );




// header and footers for various pages
$header = "include( \"header.php\" );";
$footer = "include( \"footer.php\" );";


// for admin view
$housesPerPage = 50;



// with these defaults, a $100,000 price will drop below 1 in a bit more than 8
// days (price clamped to $1 after that)

// price drop interval for auto-auctions in minutes
$auctionPriceDropInterval = 3;

// prices drop by half after this many minutes
$auctionPriceHalfLife = 720;



// how much money players start with on new lives
$playerStartMoney = 2000;



// how often absent (house not checked out for edit) player and wife get paid
//$payInterval = "0 1:00:0.000";
// originally 1 hour, experimenting with every 10 minutes
$payInterval = "0 0:10:0.000";

$playerPayAmount = 70;
$wifePayAmount = 140;


// how long the chill on a house lasts after you die there
// experimenting with 1 hour
$chillTimeout = "0 1:00:0.000";



// server shutdown mode
// causes server to respond with SHUTDOWN to most requests
// (still allows houses to be checked back in).
// Use this to weed people off of the server before installing updates, doing
// maintenance, etc.

$shutdownMode = 0;



// if server is running in perma-permadeath mode (limit on number of fresh
// starts per player), how many lives each gets.
// Set to -1 for no limit.
// Note that 1 means player gets to play one life with no fresh start
$startingLifeLimit = -1;


?>