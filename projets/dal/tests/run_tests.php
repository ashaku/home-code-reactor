<? 
	// VISUALISATION DES TESTS
	// Appel au code du projet et aux tests
	// Affiche les résultats de facon graphique
	
	require("../code/dal.php");
	require("tests.php");
	
	
	echo "<br>TU Connexion : ";	if ( tu_connect() )		echo "OK";	else echo "KO";
	echo "<br>TU Requetage : ";	if ( tu_query() )		echo "OK";	else echo "KO";
	echo "<br>TU nb lignes : ";	if ( tu_nbRows() )		echo "OK";	else echo "KO";
	echo "<br>TU Parcours : ";	if ( tu_getLine() )		echo "OK";	else echo "KO";
?>