<? 
	// LIBRAIRIE DES TESTS
	// Contient les fonctions de tests du projet
	// Le commentaire au dessus des fonctions sert de descriptif lors de l'enregistrement en base
	// le préfixe des fonctions donne son type : tu_:unitaire ; tf_:fonctionnel ; tm:metier ; tc_:critique
	// les fonctions doivent retourner true ou false
	
	
	// Lire la valeur du seed dans un fichier
	function tu_read_seed_file(){
		if ( $fs = fopen ( "seed", "r" ) ){
			$seed = fgets ( $fs, 32 );
			if ( $seed == 1 ){
				fclose ( $fs );
				return true;
			}
		}
		fclose ( $fs );
		return false;
	}
		
		
	// Ecrire dans le fichier seed
	function tu_write_seed_file(){
		if ( file_exists ( "seed" ) ) unlink ( "seed" );
		if ( $fs = fopen ( "seed", "a" ) ){
			if ( fputs ( $fs, 1 ) ){
				fclose ( $fs );
				return true;
			}
		}
		fclose ( $fs );	
		return false;
	}
	
	// Parcours un echantillon de taille maximale à la recherche de répétition du même nombre
	function tf_frequence(){
		
		$nb_echantillon = 65534;
		$min = 0; $max = 65534;
		$list = jmrandom_list($nb_echantillon,$min,$max);
		
		$start = $list[0];
		$f = 65535;
		for ($i=1; $i<=65534; $i++ ){
			if ( $list[$i] == $start ){
				$f = $i;
				continue;
			}
		}
		if ( $f == 65535 ) return true;
		return false;
	}
	
	// Teste si les nombres aux bornes sortent et si des nombres hors des bornes ne sortent pas
	function tf_bornes(){
		
		$nb_echantillon = 1000;
		$min = 1; $max = 61;
		$list = jmrandom_list($nb_echantillon,$min,$max);
		
		$b_min = false;	$b_max = false;
		foreach ( $list as $nb ){
			if ( $nb < $min || $nb > $max )	return false;
			if ( $nb == $min ) $b_min = true;
			if ( $nb == $max ) $b_max = true;
			if ( $b_min && $b_max )	return true;
		}
		return false;
	}
	
	
	// Teste 10000 nombres de 0 à 9 et compare à la distribution probable (1000 chacun)
	function tf_uniformite(){
		$nb_echantillon = 1000;
		$min = 0; $max = 9;
		$list = jmrandom_list($nb_echantillon,$min,$max);
		foreach ( $list as $nb ) $tab[$nb]++;
		foreach ( $tab as $k=>$v ){
			$pc = 1000 * $v / $nb_echantillon;
			$ecart = abs(100-$pc);
			if ( $ecart > 25 ) return false;
		}
		return true;
	}
	
?>