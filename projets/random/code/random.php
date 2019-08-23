<?
	// PROJET RANDOM
	// GENERATEUR CONGRUENTIEL LINEAIRE
	// Genere des nombres pseudo aleatoires 
	
	
	
	// Patch pair/impair
	function jmrandom ( $min=0, $max=1 ){
		$seed = microtime_seed();
		if (  ( $max - $min ) % 2 != 0 ){
			do{
				$nb = run ( $min, ($max+1), $seed );
			}while ( $nb == $max + 1 );
			return $nb;
		}else
			return run ( $min, $max, $seed );
	}
	
	
	// Retourne une liste de nombres (patché)
	function jmrandom_list ( $n=1, $min=0, $max=1 ){
		$seed = microtime_seed();
		if (  ( $max - $min ) % 2 != 0 ) $patch = true;
		//if ( $patch ) echo " PATCH! ";
		$raw = array();
		for ( $i=0; $i<$n; $i++ ){
			if ( $patch ){
				do{
					$seed = compute (  $seed );
					$nb = $seed % ($max-$min+2);
					if ( $min > 0 ) $nb += $min;
				}while ( $nb == $max + 1 );
			}else{
				$seed = compute ( $seed );
				$nb = $seed % ( ( $max - $min ) + 1 );
				if ( $min > 0 ) $nb += $min;
			}
			$raw[$i] = $nb;
		}
		return $raw;
	}
	
	
	
	
	
	// Retourne un nombre pseudo-aleatoire compris entre min et max inclus
	function run ( $min=0, $max=1, $seed=1 ){
		$seed = compute ( $seed );
		$nb = $seed % ( ( $max - $min ) + 1 );
		if ( $min > 0 ) $nb += $min;
		return $nb;
	}
	
	// Retourne un digit de 0 a 9999, basé sur les microsecondes
	function microtime_seed(){
		$t = explode(" ",microtime());
		$r2 = substr( $t[0], 4, 4 );
		return $r2;
	}
	
	// Retourne une occurence de calcul (entre 0 et modulo)
	function compute ( $seed=1 ){
		$multi = 61;
		$increment = 1;
		$modulo = 65536;//32768;
		return ( ( $seed * $multi ) + $increment ) % $modulo;
	}
	
	
	
	
	// EX-FONCTIONS SEED (Legacy)
	///////////////////////////////////////
	// Lit le fichier "seed" et retourne la valeur qu'il contient
	// ou "1" si le fichier est vide ou n'existe pas
	/*function get_seed (){
		if ( $fs = fopen ( "seed", "r" ) )
			$seed = fgets ( $fs, 32 );
		else
			$seed = 1;
		fclose ( $fs );
		return $seed;
	}
	*/
	// Enregistre la valeur courante du génerateur
	// Cette valeur sera utilisée pour la prochaine utilisation de la fonction MY_RANDOM()
	/*function save_seed ( $seed ){
		if ( file_exists ( "seed" ) ) unlink ( "seed" );
		$fs = fopen ( "seed", "a" );
		fputs ( $fs, $seed );
		fclose ( $fs );	
	}
	*/
?>