<? 
	// LIBRAIRIE DES TESTS
	// Contient les fonctions de tests du projet
	// Le commentaire au dessus des fonctions sert de descriptif lors de l'enregistrement en base
	// le préfixe des fonctions donne son type : tu_:unitaire ; tf_:fonctionnel ; tm:metier ; tc_:critique
	// les fonctions doivent retourner true ou false
	
	
	
	// Insere une ligne, met à jour la valeur, verifie le changement, supprime la ligne et verifie la suppression
	function tf_complet(){
		$db = dal_connectToDatabase();
		if ( $db ){
			$id = date("s");
			$sql = "insert into tests (id,value) values ($id,'test')";
			$ret = dal_query($db,$sql);
			// Si insertion réussie => update avec valeur specifique
			if ( $ret[0] ){
				$sql = "update tests set value='test_$id' where id=$id";
				$ret = dal_query($db,$sql);
				// Si update OK => lire pour verifier
				if ( $ret[0] ){
					$sql = "select value from tests where value='test_$id'";
					$ret = dal_query($db,$sql);
					// Si lecture OK => comparer avec valeur specifique utilisée
					if ( $ret[0] ){
						$row = dal_getLine($ret[1]);
						// Si comparaison OK => delete
						if ( $row["value"] == "test_$id" ){
							$sql = "delete from tests where value='test_$id'";
							$ret = dal_query($db,$sql);
							// Si delete OK => verifier absence de la ligne
							if ( $ret[0] ){
								$sql = "select value from tests where value='test_$id'";
								$ret = dal_query($db,$sql);
								if ( $ret[0] ){
									if ( nbRows($ret[1]) == 0 )	return true;
								}
							}
						}
					}
				}
			}
		}
		return false;
	}
	
	// Connexion à la base SQL
	function tu_connect(){
		$db = dal_connectToDatabase();
		if ( $db ){
			dal_closeDatabase( $db );
			return true;
		}else
			return false;
	} 
	
	// Requetage de la base
	function tu_query(){
		$db = dal_connectToDatabase();
		if ( $db ){
			$sql = "select * from tests";
			$ret = dal_query($db,$sql);
			if ( $ret[0] )	return true;
		}
		return false;
	} 
	
	// Nombre de resultats
	function tu_nbRows(){
		$db = dal_connectToDatabase();
		if ( $db ){
			$sql = "select * from tests";
			$ret = dal_query($db,$sql);
			if ( $ret[0] ){
				if ( dal_nbRows($ret[1]) == 1 ) return true;
			}
		}
		return false;
	}
	
	// Parcours du resultat
	function tu_getLine(){
		$db = dal_connectToDatabase();
		if ( $db ){
			$sql = "select * from tests";
			$ret = dal_query($db,$sql);
			if ( $ret[0] ){
				$row = dal_getLine($ret[1]);
				if ( $row["value"] == "test" ) return true;
			}
		}
		return false;
	}
	
	// Test de correction des chaines problématiques (accents, apostrophes, ...)
	// function tu_sanitize(){}
	
	
?>