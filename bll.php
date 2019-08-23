<?
	// Serveur::BLL
	// Couche métier
	// 
	
	
	// Accès aux données
	require("dal.php");
	
	
	// FONCTIONS FORGE
	// Créer un nouveau projet
	function create_new_project($projectName,$projectfolder,$projectDesc,$projectType){
		
		if ( $projectName == "" || $projectfolder == "" ){
			$retJson = "{\"errors\":[\"nom invalide\"]}";
		}else{
			
			// File system
			mkdir ( "projets/$projectfolder" );
			mkdir ( "projets/$projectfolder/code" );
			if ( $projectType == "js" ){
				$fp = fopen ( "projets/$projectfolder/code/$projectfolder.js", 'w+'); fclose($fp);
				$fp = fopen ( "projets/$projectfolder/code/index.html", 'w+');
				fwrite($fp, "<html>\n	<head>\n	 <title>$projectfolder</title>\n	</head>	\n");
				fwrite($fp, "	<body>\n		<script src='$projectfolder.js'></script>\n	</body>\n</html>\n");
				fclose($fp);
				$page = "index.html";
			}else{
				$fp = fopen ( "projets/$projectfolder/code/$projectfolder.php", 'w+'); fclose($fp);
				$page = "$projectfolder.php";
			}
			mkdir ( "projets/$projectfolder/doc" );
			$fp = fopen ( "projets/$projectfolder/doc/todo.txt", 'w+'); fclose($fp);
			$fp = fopen ( "projets/$projectfolder/doc/doc_technique.txt", 'w+'); fclose($fp);
			$fp = fopen ( "projets/$projectfolder/doc/doc_fonctionnelle.txt", 'w+'); fclose($fp);
			mkdir ( "projets/$projectfolder/tests" );
			$fp = fopen ( "projets/$projectfolder/tests/tests.".$projectType, 'w+');
			if ( $projectType == "php" ){
				fwrite($fp, "<?\n");
				fwrite($fp, "	// LIBRAIRIE DES TESTS\n\n");
				fwrite($fp, "	//prefixe 'tu_' pour tests unitaires\n	function tu_test_unitaire1(){\n		return [false,'message'];\n	}\n\n");
				fwrite($fp, "	//prefixe 'tf_' pour tests fonctionnels\n	function tf_test_fonctionnel1(){\n		return [false,'message'];\n	}\n\n");
				fwrite($fp, "	//prefixe 'tm_' pour tests metiers\n	function tm_test_metier1(){\n		return [false,'message'];\n	}\n\n");
				fwrite($fp, "	//prefixe 'tc_' pour tests critiques\n	function tc_test_critique1(){\n		return [false,'message'];\n	}\n");
				fwrite($fp, "\n?>");
			}else{
				fwrite($fp, "\n	// LIBRAIRIE DES TESTS\n\n");
				fwrite($fp, "	var Tests = {\n		//prefixe 'tu_' pour tests unitaires\n	tu_test_unitaire1 : function (){\n			return [false,'message'];\n		},\n\n");
				fwrite($fp, "		//prefixe 'tf_' pour tests fonctionnels\n		tf_test_fonctionnel1 : function (){\n			return [false,'message'];\n		}\n\n");
				fwrite($fp, "		//prefixe 'tm_' pour tests metiers\n");
				fwrite($fp, "		//prefixe 'tc_' pour tests critiques\n	}\n");
				fwrite($fp, "	};");
			}
			fclose($fp);
			if ( $projectType == "php" ){
				$fp = fopen ( "projets/$projectfolder/tests/run_tests.php", 'w+');
				fwrite($fp, "<? \n	// VISUALISATION DES TESTS\n\n");
				fwrite($fp, "\n	require('../code/$projectfolder.php');\n");
				fwrite($fp, "\n	require('tests.php');\n\n?>");
				fclose($fp);
			}else{
				$fp = fopen ( "projets/$projectfolder/tests/run_tests.html", 'w+');
				fwrite($fp, "<html>\n	<head>\n	 <title>TESTS $projectfolder</title>\n	</head>	\n	<body>");
				fwrite($fp, "\n\n		<script src='../code/$projectfolder.js'></script>");
				fwrite($fp, "\n		<script src='tests.js'></script>\n	</body>\n</html>\n");
				fclose($fp);
			}
			mkdir ( "projets/$projectfolder/versions" );
			
			// data base
			$db = connectToDatabase();
			$sql = "insert into projets (`type`,`date_creation`,`name`,`folder`,`desc`,`page`,`status`,`date_modif`) values ('$projectType','".date("Y-m-d")."','".$projectName."','$projectfolder','".$projectDesc."','".$page."','run','".date("Y-m-d")."')";
			$ret = SQLquery($db,$sql);
			if ( !$ret[0] )	$retJson = "{\"errors\":[\"$sql => ".$ret[1]."\"]}";
			closeDatabase($db);
		}
		return $retJson;
	}
	
	// Construire la liste des projets
	function get_projects_list(){
		$db = connectToDatabase();
		$sql = "select * from projets order by date_modif desc,name";
		$ret = SQLquery($db,$sql);
		if ( $ret[0] ){
			$retJson = get_json_from_db_res($ret[1]);
		}else{
			$retJson = "{\"errors\":[\"".$ret[1]."\"]}";
		}
		closeDatabase($db);
		return $retJson;
	}
	
	// Export complet de la forge
	function export_forge(){
		$db = connectToDatabase();
		closeDatabase($db);
	}
	
	
	
	// PROJETS
	// Retourne les infos d'un projet
	function get_project_data($projectId){
		$db = connectToDatabase();
		$sql = "select * from projets where id=".$projectId;
		$ret = SQLquery($db,$sql);
		if ( $ret[0] ){
			$row = getLine($ret[1]);
			$retJson = get_json_from_db_row($row);
		}else{
			$retJson = "{\"errors\":[\"".$ret[1]."\"]}";
		}
		closeDatabase($db);
		return $retJson;
	}
	
	// Modifier la page d'un projet
	function change_project_page($projectId,$newPage){
		$db = connectToDatabase();
		$sql = "update projets set page='$newPage' where id=".$projectId;
		$ret = SQLquery($db,$sql);
		if ( !$ret[0] )	$retJson = "{\"errors\":[\"$sql => ".$ret[1]."\"]}";
		closeDatabase($db);
		return $retJson;
	}
	
	// Modifier le statut d'un projet
	function change_project_status($projectId,$newStatus){
		$db = connectToDatabase();
		$sql = "update projets set status='$newStatus' where id=".$projectId;
		$ret = SQLquery($db,$sql);
		if ( !$ret[0] )	$retJson = "{\"errors\":[\"$sql => ".$ret[1]."\"]}";
		closeDatabase($db);
		return $retJson;
	}
	
	// Construire un tableau a partir des tickets du projet
	function get_graph_data($projectId){
		
		// preparation
		$db = connectToDatabase();
		$max=0; $val=0;
		$fonc = array();
		$bug = array();
		$dette = array();
		$jsonData = "";
		
		$sql = "select * from versions where project_id=".$projectId." order by date";
		$ret = SQLquery($db,$sql);
		if ( !$ret[0] )	$retJson = "{\"errors\":[\"$sql => ".$ret[1]."\"]}";
		else{
			$res = $ret[1];
			if ( nbRows($res) ){
				while ( $row = getLine($res) ){
					$jsonData .= "\"".$row["date"]."\":{\"version\":\"".$row["num_version"]."\",\"stocks\":[".$row["nb_func"].",".$row["nb_bug"].",".$row["nb_dette"]."]},";
					
					$fonc[$row["date"]] = $row["nb_func"];
					if ( $row["nb_func"]>$max ) $max = $row["nb_func"];
					
					$bug[$row["date"]] = $row["nb_bug"];
					if ( $row["nb_bug"]>$max ) $max = $row["nb_bug"];
					
					$dette[$row["date"]] = $row["nb_dette"];
					if ( $row["nb_dette"]>$max ) $max = $row["nb_dette"];
					
					$last_date = $row["date"];
				}
			}
			
		}
		closeDatabase($db);
		
		$retJson = "{\"max\":\"$max\",\"last_date\":\"$last_date\",\"data\":{".substr($jsonData,0,strlen($jsonData)-1)."}}";
		return $retJson;
	}
	
	
	
	// TICKETS
	// Ajouter un ticket en donnant son type (evol,bug,dette)
	function create_new_ticket($name,$desc,$type,$projectId){
		$db = connectToDatabase();
		$sql = "insert into tickets (`project_id`,`type`,`name`,`desc`,`date_open`) values ('$projectId','$type','".$name."','".$desc."','".date("Y-m-d H:i:s")."')";
		$ret = SQLquery($db,$sql);
		if ( !$ret[0] )	$retJson = "{\"errors\":[\"$sql => ".$ret[1]."\"]}";
		else{
			// MàJ projet
			$sql = "update projets set date_modif='".date("Y-m-d H:i:s")."' where id=".$projectId;
			$ret = SQLquery($db,$sql);
			if ( !$ret[0] )	$retJson = "{\"errors\":[\"$sql => ".$ret[1]."\"]}";
		}
		closeDatabase($db);
		return $retJson;
	}
	
	// Modifier la date de fermeture d'un ticket
	function close_ticket($ticketId,$projectId){
		$db = connectToDatabase();
		$sql = "update tickets set date_close='".date("Y-m-d H:i:s")."' where id=".$ticketId;
		$ret = SQLquery($db,$sql);
		if ( !$ret[0] )	$retJson = "{\"errors\":[\"$sql => ".$ret[1]."\"]}";
		else{
			// MàJ projet
			$sql = "update projets set date_modif='".date("Y-m-d H:i:s")."' where id=".$projectId;
			$ret = SQLquery($db,$sql);
			if ( !$ret[0] )	$retJson = "{\"errors\":[\"$sql => ".$ret[1]."\"]}";
		}
		closeDatabase($db);
		return $retJson;
	}
	
	// Demander la liste des ticket d'un type donné pour un projet donné
	function get_tickets_list($ticketType,$projectId){
		$db = connectToDatabase();
		// [2018-03-02 : ne plus afficher les tickets fermés (lisibilité)]
		//$sql = "select `id`,`name`,`desc`,`date_open`,`date_close` from tickets where project_id=".$projectId." and type='".$ticketType."' order by date_close,date_open";
		$sql = "select `id`,`name`,`desc`,`date_open`,`date_close` from tickets where project_id=".$projectId." and type='".$ticketType."' and date_close is NULL order by date_open";
		$ret = SQLquery($db,$sql);
		if ( $ret[0] ){
			$retJson = get_json_from_db_res($ret[1]);
		}else{
			$retJson = "{\"errors\":[\"".$ret[1]."\"]}";
		}
		closeDatabase($db);
		return $retJson;
	}
	
	
	
	// TESTS
	// Scanne le fichier de tests et met à jour la base
	function scan_tests($projectId){
		$db = connectToDatabase();
		
		// Recuperer le chemin vers les tests du projet
		$sql = "select folder,page,type from projets where id=".$projectId;
		$ret = SQLquery($db,$sql);
		if ( !$ret[0] ) return "{\"errors\":[\"".$ret[1]."\"]}";
		$row = getLine($ret[1]);
		$projectType = strtolower($row["type"]);
		$retJson = "";
		
		// Supprimer les tests existants dans la base
		$ret = SQLquery($db,"delete from tests where project_id=".$projectId);
		if ( !$ret[0] ) return "{\"errors\":[\"".$ret[1]."\"]}";
		
		// Lire le code des tests
		$fp = fopen ("projets/".$row["folder"]."/tests/tests.$projectType","r");
		$isCommented = false;
		while ( ($line = fgets($fp,4000)) !== false ){
			
			// Si le code n'est pas commenté ...
			if ( strpos($line,"/*") !== false ){
				$isCommented = true;
				$line = substr ( $line, 0, strpos($line,"/*") );
			}elseif ( strpos($line,"*/") !== false ){
				$isCommented = false;
				$line = substr ( $line, strpos($line,"*/")+2 );
			}
			if ( ! $isCommented ){
				$line = trim($line);
				if ( substr ( $line, 0, 2 ) != "//" ){
				
					// ... et que la ligne décrit une fonction 
					if ( is_new_test($line,$projectType) ){
						
						// Enregistrer le test
						$line = get_test_name ( $line, $projectType );
						$testType = substr ( $line, 0, 2 );
						$testName = substr($line,3);
						if ( $testType == "tu" )	$testType = "unitaire";
						elseif ( $testType == "tf" )$testType = "fonctionnel";
						elseif ( $testType == "tm" )$testType = "metier";
						elseif ( $testType == "tc" )$testType = "critique";
						$sql = "insert into tests (`project_id`,`type`,`name`,`desc` ) values('$projectId','$testType','$testName','".trim(substr($last,2))."')";
						$ret = SQLquery($db,$sql);
						$retJson .= ",\"$line\":\"$sql\"";
					}
					
				}
				// Enregistre la derniere ligne lue. Dans le cas d'une fonction de
				// test, la ligne precedente contient un commentaire de description
				$last = $line;
			}
		}
		$retJson = "{".substr($retJson,1)."}";
		
		closeDatabase($db);
		return $retJson;
	}
	// Indique si une ligne contient une nouvelle fonction de test
	function is_new_test($line,$type){
		if ( $type == 'js' ){
			// Pattern JS : nom_fonction : function (){}
			if ( strpos($line,' : function') ) return true;
		}else{
			// Pattern PHP : function nom_fonction()
			if ( substr($line,0,8) == "function" )	return true;
		}
		return false;
	}
	// Extrait le nom de la fonction de test
	function get_test_name($line,$type){
		if ( $type == 'js' )
			return substr( $line, 0, strpos($line,' : function'));
		else
			return trim(substr($line,9, strpos($line,'(')-9));
	}
	
	// Liste des tests
	function get_tests_list($projectId){
		$db = connectToDatabase();
		$sql = "select `id`,`name`,`type`,`desc`,`last_run`,`status`,`debug` from tests where project_id=".$projectId." order by type";
		$ret = SQLquery($db,$sql);
		if ( $ret[0] ){
			$retJson = get_json_from_db_res($ret[1]);
		}else{
			$retJson = "{\"errors\":[\"".$ret[1]."\"]}";
		}
		closeDatabase($db);
		return $retJson;
	}
	
	// Lancer un test PHP
	function run_php_test($projectFolder,$testId,$testType,$testName){
		
		// Appel du script de test concerné
		require("projets/$projectFolder/code/$projectFolder.php");
		require("projets/$projectFolder/tests/tests.php");
		
		// Construction du nom de la fonction de test
		switch ( $testType ){
			case "unitaire":	$testType="tu"; break;
			case "fonctionnel":	$testType="tf"; break;
			case "metier":	$testType="tm"; break;
			case "critique":	$testType="tc"; break;
		}
		$testFunction = $testType."_".$testName;
		
		// Execution du test
		$res = $testFunction();
		
		// Update database
		$status = $res[0] ? "OK" : "KO";
		$db = connectToDatabase();
		$sql = "update tests set last_run='".date("Y-m-d H:i:s")."',status='$status',debug='".$res[1]."' where id=$testId";
		$ret = SQLquery($db,$sql);
		if ( $ret[0] )
			$retJson = "{\"test_id\":\"$testId\",\"status\":\"$status\",\"run_date\":\"".date("Y-m-d H:i:s")."\",\"debug\":\"".$res[1]."\"}";
		else
			$retJson = "{\"error\":\"".($ret[1])."\"}";
		
		closeDatabase($db);
		return $retJson;
	}
	
	// Lancer les tests PHP d'un type
	function run_php_tests($projectId,$projectFolder,$testType){
		
		// Appel du script de test concerné
		require("projets/$projectFolder/code/$projectFolder.php");
		require("projets/$projectFolder/tests/tests.php");
		
		// Recuperer les tests du type
		$db = connectToDatabase();
		$sql = "select * from tests where project_id=$projectId and type='$testType'";
		$testsList = SQLquery($db,$sql);
		if ( $testsList[0] ){
			
			// Construction du prefixe des fonctions
			switch ( $testType ){
				case "unitaire":	$prefix="tu"; break;
				case "fonctionnel":	$prefix="tf"; break;
				case "metier":	$prefix="tm"; break;
				case "critique":	$prefix="tc"; break;
			}
			
			// Pour chaque test
			while ( $row = getLine($testsList[1]) ){
			
				// Construction du nom de la fonction de test
				$testFunction = $prefix."_".$row["name"];
				
				// Execution du test
				$res = $testFunction();
				
				// Update database
				$status = $res[0] ? "OK" : "KO";
				$sql2 = "update tests set last_run='".date("Y-m-d H:i:s")."',status='$status',debug='".$res[1]."' where id=".$row["id"];
				$update = SQLquery($db,$sql2);
				if ( !$update[0] )	$retJson = "{\"query\":\"$sql2\",\"error\":\"".($update[1])."\"}";
			}
			
		}else	$retJson = "{\"query\":\"$sql\",\"error\":\"".($testsList[1])."\"}";
		
		closeDatabase($db);
		return $retJson;
	}
	
	// Lancer tous les tests PHP
	function run_all_php_tests($projectId,$projectFolder){
		
		// Appel du script de test concerné
		require("projets/$projectFolder/code/$projectFolder.php");
		require("projets/$projectFolder/tests/tests.php");
		
		// Recuperer les tests du type
		$db = connectToDatabase();
		$sql = "select * from tests where project_id=$projectId";
		$testsList = SQLquery($db,$sql);
		if ( $testsList[0] ){
			
			// Pour chaque test
			while ( $row = getLine($testsList[1]) ){
			
				// Construction du prefixe des fonctions
				switch ( $row["type"] ){
					case "unitaire":	$prefix="tu"; break;
					case "fonctionnel":	$prefix="tf"; break;
					case "metier":	$prefix="tm"; break;
					case "critique":	$prefix="tc"; break;
				}
				
				// Construction du nom de la fonction de test
				$testFunction = $prefix."_".$row["name"];
				
				// Execution du test
				$res = $testFunction();
				
				// Update database
				$status = $res[0] ? "OK" : "KO";
				$sql2 = "update tests set last_run='".date("Y-m-d H:i:s")."',status='$status',debug='".$res[1]."' where id=".$row["id"];
				$update = SQLquery($db,$sql2);
				if ( !$update[0] )	$retJson = "{\"query\":\"$sql2\",\"error\":\"".($update[1])."\"}";
			}
			
		}else	$retJson = "{\"query\":\"$sql\",\"error\":\"".($testsList[1])."\"}";
		
		closeDatabase($db);
		return $retJson;
	}
	
	
	// Enregistrer un test JS
	function save_test_js($testId,$testStatus,$testDebug){
		
		$db = connectToDatabase();
		$sql = "update tests set last_run='".date("Y-m-d H:i:s")."',status='$testStatus',debug='$testDebug' where id=$testId";
		$ret = SQLquery($db,$sql);
		if ( $ret[0] )
			$retJson = "{\"test_id\":\"$testId\",\"status\":\"$testStatus\",\"run_date\":\"".date("Y-m-d H:i:s")."\"}";
		else
			$retJson = "{\"error\":\"".($ret[1])."\"}";
		
		closeDatabase($db);
		return $retJson;
	}
	
	
	
	// VERSIONS
	// Liste des versions d'un projet
	function get_versions_list($projectId){
		$db = connectToDatabase();
		$sql = "select `num_version`,`date`,`notes` from versions where project_id=".$projectId." order by date desc,num_version desc";
		$ret = SQLquery($db,$sql);
		if ( $ret[0] ){
			$retJson = get_json_from_db_res($ret[1]);
		}else{
			$retJson = "{\"errors\":[\"".$ret[1]."\"]}";
		}
		closeDatabase($db);
		return $retJson;
	}
	
	// Ajouter une version
	function build_project_version($projectId,$projectFolder,$numVersion,$descVersion,$copyFiles){
		
		// Récuperer nb tickets
		$db = connectToDatabase();
		$debug = "";
		
		// Récupérer la date de la derniere version
		$sql = "select max(`date`) from versions where project_id=".$projectId;
		$ret = SQLquery($db,$sql);
		if ( !$ret[0] )	return "{\"errors\":[\"Erreur de récupération de la date de la derniere version : $sql; ".$ret[1]."\"]}";
		$row = getLine ( $ret[1] );
		$dateLastVersion = $row[0];
		
		// Récupérer le nombre de tickets evolution fermés depuis derniere version (nouvelles fonctionnalités apportées par cette version)
		$sql = "select id from tickets where project_id=".$projectId." and type='evolution' and date_close>'".$dateLastVersion."'";
		$ret = SQLquery($db,$sql);
		if ( !$ret[0] ) return "{\"errors\":[\"Erreur de récupération des tickets évolution : $sql; ".$ret[1]."\"]}";
		$nbFonc = 0; $listId = "";
		while ( $row = getLine ( $ret[1] ) ){
			$nbFonc++;
			$listId .= ",".$row[0];
		}
		// marquer les tickets résolus à cette version
		if ( $nbFonc > 0 ){
			$sql = "update tickets set version='".$numVersion."' where id in(".substr($listId,1).")";
			$ret = SQLquery($db,$sql);
		}
		
		// Récupérer le nombre de tickets anomalie encore ouverts à cette version (nombre de bugs à cette version)
		$sql = "select count(id) from tickets where project_id=".$projectId." and type='correctif' and date_close='NULL'";
		$ret = SQLquery($db,$sql);
		if ( !$ret[0] ) return "{\"errors\":[\"Erreur de récupération des correctifs : $sql; ".$ret[1]."\"]}";
		$nbBugs = 0; $listId = "";
		while ( $row = getLine ( $ret[1] ) ){
			$nbBugs++;
			$listId .= ",".$row[0];
		}
		// marquer les tickets résolus à cette version
		if ( $nbBugs > 0 ){
			$sql = "update tickets set version='".$numVersion."' where id in(".substr($listId,1).")";
			$ret = SQLquery($db,$sql);
		}
		
		// Récupérer le nombre de tickets refactoring encore ouverts à cette version (niveau de dette technique)
		$sql = "select count(id) from tickets where project_id=".$projectId." and type='refactoring' and date_close<>'NULL'";
		$ret = SQLquery($db,$sql);
		if ( !$ret[0] ) return "{\"errors\":[\"Erreur de récupération des tickets refactoring : $sql; ".$ret[1]."\"]}";
		$nbDette = 0; $listId = "";
		while ( $row = getLine ( $ret[1] ) ){
			$nbDette++;
			$listId .= ",".$row[0];
		}
		// marquer les tickets résolus à cette version
		if ( $nbDette > 0 ){
			$sql = "update tickets set version='".$numVersion."' where id in(".substr($listId,1).")";
			$ret = SQLquery($db,$sql);
		}
		
		// Insertion de la version en base
		$sql = "insert into versions (project_id,num_version,date,notes,nb_func,nb_bug,nb_dette) values ($projectId,'$numVersion','".date("Y-m-d")."','".$descVersion."',$nbFonc,$nbBugs,$nbDette)";
		$ret = SQLquery($db,$sql);
		if ( !$ret[0] ) return "{\"errors\":[\"insertion impossible en base. SQL: ".$sql."; ".$ret[1]."\"]}";
		closeDatabase($db);
		
		// Creation d'une archive dans le dossier code
		if ( $copyFiles != "false" ){
			$archiveName = $projectFolder."-v".$numVersion.".zip";
			$dir_code = "projets/".$projectFolder."/code/";
			$zip = new ZipArchive(); 
			if ( $zip->open($dir_code.$archiveName, ZipArchive::CREATE) === true ){
				
				// Ajout des fichiers à la racine du dossier dans l'archive
				// TODO : parcourir récursivement le dossier code pour archiver le contenu des sous-dossiers
				$nbf = 0;
				if ( $dh = opendir($dir_code) ){
					while (($file = readdir($dh)) !== false) {
						if($file != '..'  && $file != '.'){
							$zip->addFile($dir_code.$file, $file);
							$nbf++;
						}
					}
				}
				if ( $nbf == 0 ) $debug = "aucun fichier copié";
			}else $debug = "impossible de créer le fichier archive ".$archiveName;
			$zip->close();
			
			// Copie de l'archive dans le dossier des versions du projet
			$dir_version = "projets/".$projectFolder."/versions/".$numVersion."/";
			if (!is_dir($dir_version)) mkdir ($dir_version, 0777);
			copy ( $dir_code.$archiveName , $dir_version.$archiveName );
			unlink ( $dir_code.$archiveName );
		}
		if ( $debug ) $retJson = "{\"errors\":[\"".$debug."\"]}";
		return $retJson;
	}
	
	// Fiche de vesrion
	function get_version_notes($projectId,$numVersion){
		$db = connectToDatabase();
		if ( $numVersion == "current" ){
			$sql = "select `type`,`name`,`desc` from tickets where project_id=".$projectId." and date_close<>'NULL' and version='' order by date_close";
		}else{
			$sql = "select `type`,`name`,`desc` from tickets where project_id=".$projectId." and version='".$numVersion."' order by date_close";
		}
		$ret = SQLquery($db,$sql);
		if ( $ret[0] ){
			$retJson = get_json_from_db_res($ret[1]);
		}else{
			$retJson = "{\"errors\":[\"".$ret[1]."\"]}";
		}
		closeDatabase($db);
		return $retJson;
	}
	
	// MISC
	// construit un JSON a partir d'un resultat DB
	function get_json_from_db_res($res){
		$retJson = "{\"list\":[";
		$first = true;
		while ( $row = getLine($res) ){
			if ( !$first )	$retJson .= ",";
			else			$first = false;
			$retJson .= get_json_from_db_row($row);
		}
		$retJson .= "]}";
		return $retJson;
	}
	
	// Construit un JSON a partir d'une ligne DB
	function get_json_from_db_row($row){
		$retJson = "{";
		$first2 = true;
		$i = 0;
		foreach ( $row as $k => $v ){
			if ( ++$i % 2 == 0 ){
				if ( !$first2 )	$retJson .= ",";
				else			$first2 = false;
				$retJson .= "\"$k\":\"$v\"";
			}
		}
		$retJson .= "}";
		return $retJson;
	}
?>