<? 
	// VISUALISATION DES TESTS
	// Appel au code du projet et aux tests
	// Affiche les résultats de facon graphique
	
	require("../code/random.php");
	//require("tests.php");
	
	
	//echo "<br>TF Bornes : ";	if ( tf_bornes() )		echo "OK";	else echo "KO";
	//echo "<br>TF Uniformite : ";if ( tf_uniformite() )	echo "OK";	else echo "KO";
	//echo "<br>TF Frequence : ";	if ( tf_frequence() )	echo "OK";	else echo "KO";
	echo "<br>TF List : ";		tf_list();
	echo "<br>TF Spectre : ";	tf_spectre();
	
	
	// Test spectre 3D
	// TU 1 a 8
	// TU vitesse execution
	
	
	
	
	function tf_list(){
		$nb_echantillon = 50;
		$min = 0; $max = 9;
		$list = jmrandom_list($nb_echantillon,$min,$max);
		foreach ( $list as $nb ) echo $nb.", ";
	}
	
	function tf_spectre(){
		$nb_echantillon = 200;
		$min = 0; $max = 9;
		$list = jmrandom_list($nb_echantillon,$min,$max);
		for ( $i=0; $i<$nb_echantillon; $i+=2)	$tab[$list[$i]][$list[$i+1]]++;
		echo "<table border=0 cellspacing=0>";
		for ( $i=$min; $i<=$max; $i++ ){
			echo "<tr height=30>";
			for ( $j=$min; $j<=$max; $j++ ){
				echo "<td width=28 style='text-align:center;border:solid 1px #ccc;'>".$tab[$i][$j]."</td>";
			}
			echo "</tr>";
		}
		echo "</table>";
	}
?>