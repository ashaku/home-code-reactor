<?
	// Serveur::Webservice
	// Recoit un numero de fonction par variable url "f"
	// Execute la fonction demandée, retourne éventuellement un jeu de données JSON
	
	// Accès aux données
	require("bll.php");
	
	// Lecture de la fonction demandée
	$json = "";
	$function = $_GET['f'];
	switch ( $function ){
		
		// Fonctions FORGE
		// Nouveau projet
		case 0 :
			$projectType = strtolower($_GET['t']);
			$projectName = htmlentities($_GET['n'],ENT_QUOTES);
			$projetfolder = $_GET['r'];
			$projetDesc = htmlentities($_GET['d'],ENT_QUOTES);
			$json = create_new_project($projectName,$projetfolder,$projetDesc,$projectType);
			break;
		
		// Liste des projets
		case 1 :
			$json = get_projects_list();
			break;
			
			
		
		// Fonctions PROJET
		// Infos generales d'un projet
		case 2 :
			$projectId = $_GET['p'];
			$json = get_project_data($projectId);
			break;
		
		// Modifier la home d'un projet
		case 15 : 
			$projectId = $_GET['p'];
			$projectStatus = $_GET['s'];
			$json = change_project_status($projectId,$projectStatus);
			break;
			
		// Modifier la home d'un projet
		case 6 : 
			$projectId = $_GET['p'];
			$projectPage = $_GET['h'];
			$json = change_project_page($projectId,$projectPage);
			break;
			
		// Recuperer les infos pour graphique
		case 7 : 
			$projectId = $_GET['p'];
			$json = get_graph_data($projectId);
			break;
			
		
		
		// Fonctions TICKETS
		// Créer un ticket
		case 3 :
			$funcType = $_GET['t'];
			$funcName = htmlentities($_GET['n'],ENT_QUOTES);
			$funcDesc = htmlentities($_GET['d'],ENT_QUOTES);
			$projectId = $_GET['p'];
			$json = create_new_ticket($funcName,$funcDesc,$funcType,$projectId);
			break;
		
		// Liste de Tickets
		case 4 :
			$ticketType = $_GET['t'];
			$projectId = $_GET['p'];
			$json = get_tickets_list($ticketType,$projectId);
			break;
			
		// Cloture d'un ticket
		case 5 :
			$ticketId = $_GET['i'];
			$projectId = $_GET['p'];
			$json = close_ticket($ticketId,$projectId);
			break;
		
		
		
		// Fonctions TESTS
		// Scan des test, update de la base
		case 8 :
			$projectId = $_GET['p'];
			$json = scan_tests($projectId);
			break;
		
		// Liste des tests d'un projet
		case 9 :
			$projectId = $_GET['p'];
			$json = get_tests_list($projectId);
			break;
			
		// Lancer un ou plusieurs tests PHP
		case 10 :
			$projectId = $_GET['p'];
			$projectFolder = $_GET['d'];
			$testType = $_GET['t'];
			$testName = $_GET['n'];
			$testId = $_GET['i'];
			// Si id du test => test unique
			if ( $testId ){
				$json = run_php_test($projectFolder,$testId,$testType,$testName);
			// Si le type est défini : lancer tous les tests de ce type
			}elseif ( $testType ){
				$json = run_php_tests($projectId,$projectFolder,$testType);
			// Sinon => lancer tous les tests
			}else{
				$json = run_all_php_tests($projectId,$projectFolder);
			}
			break;
			
		// Enregistrer un test JS
		case 13 :
			$testId = $_GET['i'];
			$testStatus = $_GET['r'];
			$testDebug = $_GET['d'];
			$json = save_test_js($testId,$testStatus,$testDebug);
			break;
			
			
		// Fonctions VERSIONS
		// Lister les versions
		case 11 :
			$projectId = $_GET['p'];
			$json = get_versions_list($projectId);
			break;
			
		// ajouter une version
		case 12 :
			$projectId = $_GET['p'];
			$projectFolder = $_GET['d'];
			$numVersion = $_GET['v'];
			$copyFiles = $_GET['s'];
			$descVersion = htmlentities($_GET['c'],ENT_QUOTES);
			$json = build_project_version($projectId,$projectFolder,$numVersion,$descVersion,$copyFiles);
			break;
			
		// Fiche de version
		case 14 :
			$projectId = $_GET['p'];
			$numVersion = $_GET['v'];
			$json = get_version_notes($projectId,$numVersion);
			
	}
	
	
	// Renvoyer resultat JSON
	header('Content-type: application/json');
	echo json_encode($json);
	
?>