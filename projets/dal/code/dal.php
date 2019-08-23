<?
	// Serveur:DAL
	// Couches d'accès aux données
	// Fournit la connection, le requetage et le parsing mySQL
	
	
	// Connect to known database
	function dal_connectToDatabase(){
		
		$baseHost = "127.0.0.1";	
		$baseName = "dal";	
		$baseLogin = "root";	
		$basePassword = "";
		
		$db = mysqli_connect($baseHost, $baseLogin, $basePassword, $baseName);
		if ( mysqli_connect_errno() ){
			//echo "Failed to connect to MySQL: ".mysqli_connect_error();
			return false;
		}else{
			// set default charset to UTF8
			dal_query ( $db, 'SET NAMES utf8' );
			
			return $db;
		}
	}
	
	// Close database
	function dal_closeDatabase( $db ) {		mysqli_close ( $db );	}
	
	
	
	///////////////////////////////////////////////
	////////////// QUERYING FUNCTIONS /////////////
	///////////////////////////////////////////////
	
	// Query the base, catch errors
	function dal_query ( $db, $sqlQuery ){
		if ( $recordSet = mysqli_query ( $db, $sqlQuery ) ){
			return array(true,$recordSet);
		}else{
			return array(false,mysqli_error($db));
		}
	}
	
	// ALIASES
	function dal_nbRows ( $res ) {		return mysqli_num_rows ( $res );	}
	function dal_getLine ( $res ) {		return mysqli_fetch_array ( $res );	}
	
?>