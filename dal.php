<?
	// Serveur:DAL
	// Couches d'accès aux données
	// Fournit la connection, le requetage et le parsing mySQL
	
	
	// Connect to known database
	function connectToDatabase(){
		
		$baseHost = "127.0.0.1";	
		$baseName = "forge";	
		$baseLogin = "root";	
		$basePassword = "";
		
		$db = mysqli_connect($baseHost, $baseLogin, $basePassword, $baseName);
		if ( mysqli_connect_errno() )	echo "Failed to connect to MySQL: ".mysqli_connect_error();
		
		// set default charset to UTF8
		SQLquery ( $db, 'SET NAMES utf8' );
		
		return $db;
	}
	
	// Close database
	function closeDatabase( $db ) {		mysqli_close ( $db );	}
	
	
	
	///////////////////////////////////////////////
	////////////// QUERYING FUNCTIONS /////////////
	///////////////////////////////////////////////
	
	// Query the base, catch errors
	function SQLquery ( $db, $sqlQuery ){
		if ( $recordSet = mysqli_query ( $db, $sqlQuery ) ){
			return array(true,$recordSet);
		}else{
			return array(false,mysqli_error($db));
		}
	}
	
	// ALIASES
	function nbRows ( $res ) {		return mysqli_num_rows ( $res );	}
	function getLine ( $res ) {		return mysqli_fetch_array ( $res );	}
	
?>