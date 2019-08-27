// Client::UI
// Script Passe-Plat
// Convertit les input utilisateur en fonctions du système

var currentProject;
var currentProjectFolder;
var dataRetourJSON;
var projectStartDate;
var currentProjectType;
var fullDesc = false;
var height_desc;
var fullTickets = false;
var height_tickets;
var fullTests = false;
var height_tests;
var fullVersions = false;
var height_versions;

function start(){
	$("#new_project_name").val("");
	$("#new_project_folder").val("");
	display_projects_list();
	$("#project>div").css("opacity","0");
	$("#new_func_name").val("");
	$("#new_func_desc").val("");
	$("#tickets").css("height","45px");
	$("#project_status_change").hide();
	$("#version_detail").hide();
}

// WEBSERVICE : Appelle une URL webservice et retourne un JSON de données
function call_webservice ( fonction, parm ){
	var url = "forge.php?f="+fonction;
	if ( parm != undefined ) url += parm;
	console.log("WS:"+url);
	$.ajax({
       url : url,
       type : 'GET',
       dataType : 'json',
	   async : false,
       success : function(retourJSON, statut){
		dataRetourJSON = JSON.parse(retourJSON);
	   }
	});
}



// FORGE
// Créer un nouveau projet
function add_new_project(){
	
	// Envoyer le nom du projet et du dossier
	var projectType = $("#new_project_type").val();
	var projectName = $("#new_project_name").val();
	var projectFolder = $("#new_project_folder").val();
	var projectDesc = $("#new_project_desc").val();
	var parm = "&n="+projectName+"&r="+projectFolder+"&t="+projectType+"&d="+projectDesc;
	call_webservice(0,parm);
	// Gestion erreur
	if ( dataRetourJSON != null ){
		$("#new_project").css("background-color","#733");
		$("#new_project").append("<div class='error'>"+dataRetourJSON.errors[0]+"</div>");
		console.log("Erreur ajout projet : "+dataRetourJSON.errors[0]);
	}else{
		console.log("ajout projet OK");
	}
	
	$("#new_project_name").val("");
	$("#new_project_folder").val("");
	$("#new_project_desc").val("");
	display_projects_list();
}

// Afficher la liste des projets
function display_projects_list(){
	
	// Récupérer liste des projets
	call_webservice(1);
	
	// Construire le code html de la liste
	var i,html;
	if ( dataRetourJSON.errors != undefined ){
		html = dataRetourJSON.errors[0];
	}else{
		html = "<table cellpadding='0' cellspacing='0'>";
		for (i=0;i<dataRetourJSON.list.length;i++){
			html += "<tr><td class='statutProjet'><img src='img/"+dataRetourJSON.list[i].status+".png' width='16px'/></td><td><a id='nav_"+dataRetourJSON.list[i].id+"' href='#' ";
			html += "onclick='load_project("+dataRetourJSON.list[i].id+")'>"+dataRetourJSON.list[i].name+"</a></td>";
			if ( dataRetourJSON.list[i].date_modif != "0000-00-00" ) html += "<td class='dateModif'>"+dataRetourJSON.list[i].date_modif+"</td>";
			html += "</tr>";
		}
		html += "</table>";
	}
	
	// Afficher liste
	$("#projects_list").html(html);
}



// DETAIL D'UN PROJET
// Charge un projet selectionné
function load_project(projectId){
	
	// Enregistre l'id du projet en cours
	currentProject = projectId;
	
	// Animations de chargement de projet
	$(".error").remove();
	$("#version_detail").hide();
	//$("#project>div").animate({opacity:0});
	$("#project>div").css("opacity","0");
	
	// Recupere les données générales
	call_webservice(2,"&p="+projectId);
	tab = dataRetourJSON.date_creation.split(' ');
	projectStartDate = tab[0];
	currentProjectFolder = dataRetourJSON.folder;
	currentProjectType = dataRetourJSON.type;
	
	// Affiche données générales
	$("#project_status").attr("src","img/"+dataRetourJSON.status+".png");
	$("#project_name").html("<a href='projets/"+currentProjectFolder+"/code/"+dataRetourJSON.page+"'>"+dataRetourJSON.name+"</a>");
	$("#desc_text").html(dataRetourJSON.desc);
	height_desc = $("#desc_text").css("height");
	$("#project_desc").css("height","100px");
	$("#project_folder").text(dataRetourJSON.folder);
	$("#project_homepage").val(dataRetourJSON.page);
	fullDesc = false;
	
	// Affichage des tickets
	display_graphique_tickets();
	display_ticket_list(1);
	display_ticket_list(2);
	display_ticket_list(3);
	var n = $("div#tickets_ctn div.ticket_list tr").size();
	$("#tickets .block_title span").html("Tickets ("+n+")");
	height_tickets = $("#tickets_ctn").css("height");
	fullTickets = false;
	$("#tickets").css("height","45px");
	
	// Affichage des tests
	display_tests_list();
	height_tests = $("#tests_ctn").css("height");
	//$("#tests").css("height","45px");
	fullTests = true;
	if ( currentProjectType == "JS" ) page = "index.html"; else page="run_tests.php";
	$("#tests .block_title span").attr("onclick","window.location='projets/"+currentProjectFolder+"/tests/"+page+"'");
	
	// Versions
	get_project_versions();
	fullVersions = false;
	
	$("#project>div").animate({opacity:1});
}

// Affiche le menu de changement de statut
function display_status_change(){
	$("#project_status_change").show();
}

// Change le statut du projet
function change_project_status(statut){
	// query WS
	var parm = "&p="+currentProject+"&s="+statut;
	call_webservice(15,parm);
	
	// MàJ affichage
	display_projects_list();
	$("#project_status").attr("src","img/"+statut+".png");
	$("#project_status_change").hide();
}

// Modifie la page d'acceuil du projet
function change_project_page(){
	var parm = "&p="+currentProject+"&h="+$("#project_homepage").val();
	call_webservice(6,parm);
}

// Graphique de vie du projet
function display_graphique_tickets(){
	
	// Requeter données des 3 courbes
	call_webservice(7,"&p="+currentProject);
	
	// Preparer dessin
	var today = new Date();
	var width=500, height=200, marge=20, marge_nom=50;
	var max = dataRetourJSON.max;
	var unit_y = (height-marge*2) / max;
	var nb_jours = datediff(projectStartDate,dataRetourJSON.last_date,"days");
	var unit_x = (width-marge*2-marge_nom) / nb_jours;
	var org_x=marge,org_y=height-marge;
	var c = document.getElementById("graph_projet");
	c.width = width;
	c.height = height;
	var ctx = c.getContext("2d");
	//ctx.translate(0.5,0.5);
	ctx.fillStyle = "#2f2d24";
	ctx.fillRect(0, 0, width, height);
	
	// Axes
	ctx.lineWidth = 1;
	ctx.strokeStyle = "#bfbdb4";
	
	// Echelle ordonnée
	ctx.strokeStyle = "#4f4d44";
	ctx.fillStyle = "#bfbdb4";
	for ( i=0; i<=max; i++ ){
		ctx.beginPath();
		ctx.moveTo(org_x,org_y-unit_y*i);
		ctx.lineTo(width-marge,org_y-unit_y*i);
		ctx.stroke();
		ctx.fillText(i,org_x-15,org_y-unit_y*i+5)
	}
	
	// Pour chaque version
	var px,py;
	var lastFoncX=org_x,lastFoncY=org_y,lastBugX=org_x,lastBugY=org_y+1,lastDetteX=org_x,lastDetteY=org_y-1;
	var lastVersionX=-50, numVersion,nbFonc,nbBug,nbDette;
	ctx.font="11px segoe ui";
	ctx.beginPath();
	for ( key in dataRetourJSON.data ){
		
		// Tracer repere vertical
		px = org_x + parseInt(datediff(projectStartDate,key,"days"))*unit_x;
		ctx.moveTo(px,org_y);
		ctx.lineTo(px,marge);
		
		// Inscrire num version - date
		if ( px > lastVersionX+40 ){
			lastVersionX = px;
			dv = key.split('-');
			dateVersion = dv[2]+"/"+dv[1]+"/"+dv[0].substring(2);
			ctx.fillText(dateVersion,px-22,height-2);
			numVersion = dataRetourJSON.data[key].version;
			ctx.fillText(numVersion,px-12,16);
		}
	}
	ctx.stroke();
	
	// Stock "Fonctionnalités"
	ctx.lineWidth = 2;
	ctx.strokeStyle = "#af9";
	ctx.beginPath();
	for ( key in dataRetourJSON.data ){
		
		nbFonc = dataRetourJSON.data[key].stocks[0];
		px = org_x + parseInt(datediff(projectStartDate,key,"days"))*unit_x;
		py = org_y - parseInt(nbFonc)*unit_y;
		// Marquer point
		//ctx.arc(px,py,5,0.65*Math.PI,0.35*Math.PI);
		// Tracer ligne
		ctx.moveTo(lastFoncX,lastFoncY);
		ctx.lineTo(px,py);
		lastFoncX = px;
		lastFoncY = py;
	}
	ctx.stroke();
		
	// Stock "Bugs"
	ctx.strokeStyle = "#f65";
	ctx.beginPath();
	for ( key in dataRetourJSON.data ){
		nbBug = dataRetourJSON.data[key].stocks[1];
		px = org_x + parseInt(datediff(projectStartDate,key,"days"))*unit_x;
		py = org_y + 1 - parseInt(nbBug)*unit_y;
		// Tracer ligne
		ctx.moveTo(lastBugX,lastBugY);
		ctx.lineTo(px,py);
		lastBugX = px;
		lastBugY = py;
	}
	ctx.stroke();
	
	// Stock "Dette technique"
	ctx.strokeStyle = "#acf";
	ctx.beginPath();
	for ( key in dataRetourJSON.data ){
		nbDette = dataRetourJSON.data[key].stocks[2];
		px = org_x + parseInt(datediff(projectStartDate,key,"days"))*unit_x;
		py = org_y - 1 - parseInt(nbDette)*unit_y;
		// Tracer ligne
		ctx.moveTo(lastDetteX,lastDetteY);
		ctx.lineTo(px,py);
		lastDetteX = px;
		lastDetteY = py;
	}	
	ctx.stroke();
}



// TICKETS
// Créer un nouveau ticket
function add_new_ticket(type){
	
	// Recuperer info ticket
	var ticketName;
	var ticketDesc;
	var divName;
	switch(type){
		case 1 :	ticketName = $("#new_func_name").val();
					ticketDesc = $("#new_func_desc").val();
					ticketType = "evolution";
					divName = "new_functionnality";
					break;
		case 2 :	ticketName = $("#new_bug_name").val();
					ticketDesc = $("#new_bug_desc").val();
					ticketType = "correctif";
					divName = "new_anomaly";
					break;
		case 3 :	ticketName = $("#new_ref_name").val();
					ticketDesc = $("#new_ref_desc").val();
					ticketType = "refactoring";
					divName = "new_debt";
					break;
	}
	var parm = "&p="+currentProject+"&n="+ticketName+"&d="+ticketDesc+"&t="+ticketType;
	call_webservice(3,parm);
	
	// Gestion erreur
	if ( dataRetourJSON != null )
		$("#"+divName).append("<div class='error'>"+dataRetourJSON.errors[0]+"</div>");
	else{
		
		// Raz des champs
		switch(type){
			case 1 :	$("#new_func_name").val("");
						$("#new_func_desc").val("");
						break;
			case 2 :	$("#new_bug_name").val("");
						$("#new_bug_desc").val("");
						break;
			case 3 :	$("#new_ref_name").val("");
						$("#new_ref_desc").val("");
						break;
		}
		
		// Rafraichissement
		display_graphique_tickets();
		display_ticket_list(type);
		var n = $("div#tickets_ctn div.ticket_list tr").size();
		$("#tickets .block_title span").html("Tickets ("+n+")");
	}
	
	// recalcul de la hauteur
	height_tickets = $("#tickets_ctn").css("height");
	$("#tickets").css("height",height_tickets);
}

// Modifier le ticket (date_cloture)
function end_ticket(ticketId,type){
	
	var parm = "&i="+ticketId+"&p="+currentProject;
	call_webservice(5,parm);
	display_ticket_list(type);
	
	// Met a jour la hauteur du conteneur de tickets
	var h = $("#tickets").css("height");
	h = substring(h,0,h,length-2);
	h = parseInt(h)-46;
	$("#tickets").css("height",h+"px");
	
	// Met a jour le nombre de tickets (1 de moins)
	var n = $("div#tickets_ctn div.ticket_list tr").size();
	$("#tickets .block_title span").html("Tickets ("+n+")");
	
	// Met a jour la liste des tickets (nouvelle date de modif pour le projet)
	//display_projects_list();
}

// Afficher la liste des tickets d'un type donné
function display_ticket_list(type){
	
	// parametrer type
	var enumDB;
	switch(type){
		case 1 : enumDB = "evolution";		break;
		case 2 : enumDB = "correctif";		break;
		case 3 : enumDB = "refactoring";	break;
	}
	// Récupérer liste
	var parm = "&t="+enumDB+"&p="+currentProject;
	call_webservice(4,parm);
	
	// Construire le code html de la liste
	var i,html,tab;
	if ( dataRetourJSON.errors != undefined ){
		html = dataRetourJSON.errors[0];
	}else{
		html = "<table cellspacing=0 class='"+enumDB+"'>";
		for (i=0;i<dataRetourJSON.list.length;i++){
			tab = dataRetourJSON.list[i].date_open.split(' ');
			
			// Ticket fermé
			if ( dataRetourJSON.list[i].date_close.length > 0 && dataRetourJSON.list[i].date_close.substr(0,4) != "0000" ){
				// Titre + texte du ticket
				html += "<tr class='close'><td class='ticket_col_desc'>";
				html += "<span>"+dataRetourJSON.list[i].name+"</span> "+dataRetourJSON.list[i].desc.substr(0,100-dataRetourJSON.list[i].name.length);
				// Date ouverture
				html += "</td><td class='ticket_col_date'>";
				html += "<span>"+convert_date_to_fr(tab[0])+"</span>";
				// Date cloture
				html += "<td class='ticket_col_date'>";
				var tab = dataRetourJSON.list[i].date_close.split(' ');
				html += "<span>"+convert_date_to_fr(tab[0])+"</span>";
				html += "</td></tr>";
			
			// Ticket ouvert
			}else{
				// Titre + texte du ticket
				html += "<tr class='open'><td class='ticket_col_desc'>";
				html += "<b>"+dataRetourJSON.list[i].name+"</b><br>"+dataRetourJSON.list[i].desc;
				// Date ouverture
				html += "</td><td class='ticket_col_date'>";
				html += "<b>"+convert_date_to_fr(tab[0])+"</b><br>"+tab[1]+"</td>";
				// Bouton cloture
				html += "<td class='ticket_col_date'>";
				html += "<div class='list_btn' onclick='end_ticket("+dataRetourJSON.list[i].id+","+type+")'>fermer</div>";
				html += "</td></tr>";
			}
		}
		html += "</table>";
	}
	
	// Afficher liste
	switch(type){
		case 1 : $("#func_list").html(html);	break;
		case 2 : $("#bug_list").html(html);		break;
		case 3 : $("#ref_list").html(html);		break;
	}
}



// TESTS
// Met a jour la liste des tests du projet
function sync_tests(){
	var parm = "&p="+currentProject;
	call_webservice(8,parm);
	console.log(dataRetourJSON);
	display_tests_list();
}

// Affiche la liste des tests
function display_tests_list(){
	call_webservice(9,"&p="+currentProject);
	var html="<table>", oldType="", type, nb=0;
	$("#tests_list").html("");
	if ( currentProjectType == 'JS' ){
		$("#tests #script_ctn").html("");
		$("#tests #script_ctn").append("<script src='projets/"+currentProjectFolder+"/code/"+currentProjectFolder+".js'></script>");
		$("#tests #script_ctn").append("<script src='projets/"+currentProjectFolder+"/tests/tests.js'></script>");
	}
	$("#tests .block_title span").html("Tests ("+dataRetourJSON.list.length+")");
	for (i=0;i<dataRetourJSON.list.length;i++){
		type = dataRetourJSON.list[i].type;
		
		// Nouvelle categorie
		if ( type != oldType ){
			
			// remplir la liste de test de la categorie precedente
			if ( oldType != "" ){
				$("#tests_"+oldType+"s").html(html+"</table>");
				$("#section_"+oldType+" span").html(oldType+"s ("+nb+")");
				nb = 0;
			}
			html = "<table cellspacing=0>";
			
			// Ajouter la nouvelle categorie
			$("#tests_list").append("<div class='section_title' id='section_"+type+"'> <span>"+type+"s ("+nb+")</span> &nbsp; <img width=18 height=18 src='img/run_test.png' onclick='run_tests(\""+type+"\")' title='Lancer tous les tests "+type+"s'/></div>");
			$("#tests_list").append("<div id='tests_"+type+"s'></div>");
			oldType = type;
		}
		html += "<tr id='test_"+dataRetourJSON.list[i].id+"'><td class='test_name' title='"+dataRetourJSON.list[i].desc+"'>"+dataRetourJSON.list[i].name+"</td>";
		html += "<td class='test_status'><img src='img/test"+dataRetourJSON.list[i].status+".png' title='"+dataRetourJSON.list[i].debug+"'/></td>";
		html += "<td class='test_date'>"+convert_datetime_to_fr(dataRetourJSON.list[i].last_run)+"</td>";
		html += "<td class='test_run'><img width=16 height=16 src='img/run_test.png' onclick='run_test(\""+dataRetourJSON.list[i].id+"\",\""+type+"\",\""+dataRetourJSON.list[i].name+"\")' title='Lancer ce test'/></td></tr>";
		nb++;
	}
	// remplir la liste de test de la categorie precedente
	$("#tests_"+oldType+"s").html(html+"</table>");
	$("#section_"+oldType+" span").html(oldType+"s ("+nb+")");
}

// Lancer un test
function run_test(id,type,test){
	
	if ( currentProjectType == 'JS' ){
		// Appel direct aux fonctions de tests du projet JS
		switch ( type ){
			case "unitaire":	type="tu"; break;
			case "fonctionnel":	type="tf"; break;
			case "metier":	type="tm"; break;
			case "critique":	type="tc"; break;
		}
		var fct_name = type+"_"+test;
		res_test = Tests[fct_name]();
		var test_status = res_test[0]?"OK":"KO";;
		// Appel au webservice pour enregistrer le test JS
		var parm = "&p="+currentProject+"&i="+id+"&r="+test_status+"&d="+res_test[1];
		call_webservice(13,parm);
		$("#test_"+id+" .test_date").html(convert_datetime_to_fr(dataRetourJSON.run_date));
		$("#test_"+id+" .test_status").html("<img src='img/test"+test_status+".png' title='"+res_test[1]+"'/>");
		
	}else{
		// Appel au webservice pour jouer le test PHP
		var parm = "&p="+currentProject+"&d="+currentProjectFolder+"&i="+id+"&t="+type+"&n="+test;
		call_webservice(10,parm);
		$("#test_"+dataRetourJSON.test_id+" .test_date").html(convert_datetime_to_fr(dataRetourJSON.run_date));
		$("#test_"+dataRetourJSON.test_id+" .test_status").html("<img src='img/test"+dataRetourJSON.status+".png' title='"+dataRetourJSON.debug+"'/>");
	}
}

// Lancer tous les tests d'un type
function run_tests(type){
	$("#tests_"+type+"s tr").each(function(num,obj){
		id=obj.id.substr(5);
		name = $("#"+obj.id+" .test_name").html();
		name = name.substr(0,name.length);
		run_test(id,type,name);
	});
}

// Lancer tous les tests
function run_all_tests(){
	$("#tests_list .section_title").each(function(num,obj){
		type=obj.id.substr(8);
		run_tests(type);
	});
}



// VERSIONS
// Liste des versions du projet
function get_project_versions(){
	// query ws
	call_webservice(11,"&p="+currentProject);
	var html,tab,nextNum="",first=true;
	if ( dataRetourJSON.list.length > 0 ){
		html="<table width='100%'>";
		for (i=0;i<dataRetourJSON.list.length;i++){
			html += "<tr onmouseover='display_version_detail(\""+dataRetourJSON.list[i].num_version+"\")' onmouseout='hide_version_detail()'><td>"+dataRetourJSON.list[i].num_version+"</td><td>"+convert_date_to_fr(dataRetourJSON.list[i].date)+"</td><td>"+dataRetourJSON.list[i].notes+"</td></tr>";
			if ( first ) {
				nextNum = dataRetourJSON.list[i].num_version;
				first = false;
			}
		}
		html += "</table>";
		$("#versions_list").html(html);
	}else{
		$("#versions_list").html("");
	}
	// update num_version
	if ( nextNum.length > 0 ){
		tab = nextNum.split('.');
		if ( tab[1].length > 0 ){
			d1 = parseInt(tab[1]);
			nextNum = tab[0]+'.'+parseInt(d1+1);
		}else{
			d0 = parseInt(tab[0]);
			nextNum = parseInt(d0+1)+".0";
		}
		if ( tab[2] ) nextNum += "."+tab[2];
		if ( tab[3] ) nextNum += "."+tab[3];
	}else{
		nextNum = "1.0.0";
	}
	$("#new_version_num").val(nextNum);
	$("#new_version_desc").val("");
	height_versions = $("#versions_ctn").css("height");
}

// Versionner le projet
function add_new_version(){
	var parm  = "&p="+currentProject+"&d="+currentProjectFolder+"&v="+$("#new_version_num").val();
		parm += "&c="+$("#new_version_desc").val()+"&s="+$("#new_version_code").is(':checked');
	
	call_webservice(12,parm);
	if ( dataRetourJSON != null ){
		$("#new_version").append(dataRetourJSON.errors[0]);
	}else{
		get_project_versions();
		display_graphique_tickets();
	}
}

// Affiche la liste des tickets d'une version
function display_version_detail ( numVersion ){
	call_webservice(14,"&p="+currentProject+"&v="+numVersion);
	var html_tickets="",html_titre="",nbType=[];
	nbType['evolution'] = 0;
	nbType['correctif'] = 0;
	nbType['refactoring'] = 0;
	if ( dataRetourJSON.list.length > 0 ){
		for (i=0;i<dataRetourJSON.list.length;i++){
			html_tickets += "<div class='"+dataRetourJSON.list[i].type+"'><b>"+dataRetourJSON.list[i].name+" : </b>"+dataRetourJSON.list[i].desc+"</div>";
			nbType[dataRetourJSON.list[i].type]++;
		}
	}
	if ( html_tickets != "" ){
		html_titre = "<div><b>Notes de version "+numVersion+"</b>"
		if ( nbType['evolution'] > 0 ) html_titre += " &nbsp; +"+nbType['evolution']+" évol";
		if ( nbType['correctif'] > 0 ) html_titre += " &nbsp; -"+nbType['correctif']+" bug"
		if ( nbType['refactoring'] > 0 ) html_titre += " &nbsp; -"+nbType['refactoring']+" dette";
		html_titre += "</div>";
		$("#version_detail").html(html_titre+html_tickets);
		$("#version_detail").show();
		//Ajuster hauteur 
		// TODO : Fix height calculation
		/*var note_height = parseInt($("#version_detail").css("height").replace('px',''));
		var height_tests_list = parseInt($("#tests_list").css("height").replace('px',''));
		var height_versions_list = parseInt($("#versions_list").css("height").replace('px',''));
		var list_height = Math.max(height_tests_list,height_versions_list);
		if ( note_height > list_height ){
			var note_top = parseInt($("#versions_list").css("top").replace('px',''));
			var diff = (note_height-list_height);
			$("#version_detail").css("top",(note_top-diff)+"px");
		}else{
			//var list_top = parseInt($("#versions_list").css("top").replace('px',''));
			//if ( note_top < list_top )
				$("#version_detail").css("top",$("#versions_list").css("top"));
		}*/
	}
}

// Masque la fiche de version
function hide_version_detail(){
	
	$("#version_detail").hide();
}



// INPUT UTILISATEUR
// Remplit le nom du dossier à partir du nom du projet
function handle_key_folder(e){
	var r = $("#new_project_name").val().toLowerCase();
	var nextMaj = false;
	var folderName = "";
	var car;
	//r = r.replace(new RegExp(/\s/g),"");
    r = r.replace(new RegExp(/[àáâãäå]/g),"a");
    r = r.replace(new RegExp(/æ/g),"ae");
    r = r.replace(new RegExp(/ç/g),"c");
    r = r.replace(new RegExp(/[èéêë]/g),"e");
    r = r.replace(new RegExp(/[ìíîï]/g),"i");
    r = r.replace(new RegExp(/ñ/g),"n");                
    r = r.replace(new RegExp(/[òóôõö]/g),"o");
    r = r.replace(new RegExp(/œ/g),"oe");
    r = r.replace(new RegExp(/[ùúûü]/g),"u");
    r = r.replace(new RegExp(/[ýÿ]/g),"y");
    //r = r.replace(new RegExp(/\W/g),"");	
	for ( i=0; i<r.length; i++ ){
		car = r.charCodeAt(i);
		if ( car == 32 )	nextMaj = true;
		if ( (car>47 && car<58) || (car>96 && car<123) ){
			if ( nextMaj ){
				folderName += r[i].toUpperCase();
				nextMaj = false;
			}
			else			folderName += r[i];
		}
	}
	$("#new_project_folder").val(folderName);
	return false;
}

// Création d'un projet à l'appui de "enter" dans la description
function handle_key_desc(e){
    if ( e.wich == 13 || e.keyCode == 13 )	add_new_project();
    return false;
}

// Modifier la homepage à l'appui de "enter"
function handle_key_page(e){
    if ( e.wich == 13 || e.keyCode == 13 )	change_project_page();
    return false;
}

// Création d'un ticket à l'appui de "enter"
function handle_key_ticket(e,type){
    if ( e.wich == 13 || e.keyCode == 13 )	add_new_ticket(type);
    return false;
}

// Création d'une version à l'appui de "enter"
function handle_key_version(e){
    if ( e.wich == 13 || e.keyCode == 13 )	add_new_version();
    return false;
}

// Aggrandit/retrecit la description du projet
function toggle_desc(){
	if (fullDesc){
		$("#project_desc").animate({height:"100px"});
		fullDesc = false;
	}else{
		$("#project_desc").animate({height:height_desc});
		fullDesc = true;
	}
}

// Aggrandit/retrecit la zone ticket
function toggle_tickets(){
	if (fullTickets){
		$("#tickets").animate({height:"45px"});
		$("#tickets_ctn .project_section").animate({opacity:0});
		fullTickets = false;
	}else{
		$("#tickets").animate({height:height_tickets});
		$("#tickets_ctn .project_section").animate({opacity:1});
		fullTickets = true;
	}
}

// Aggrandit/retrecit la zone ticket
function toggle_tests(){
	if (fullTests){
		$("#tests").animate({height:"45px"});
		fullTests = false;
	}else{
		$("#tests").animate({height:height_tests});
		fullTests = true;
	}
}



// 2017-01-05 => 05/01/2017
function convert_date_to_fr(strDate){
	return strDate.substr(8,2)+"/"+strDate.substr(5,2)+"/"+strDate.substr(0,4);
}
// 2017-01-05 => 05/01/2017
function convert_datetime_to_fr(strDate){
	return strDate.substr(8,2)+"/"+strDate.substr(5,2)+"/"+strDate.substr(0,4)+" "+strDate.substr(10);
}

/*	DateFormat month/day/year hh:mm:ss
	ex. datediff('01/01/2011 12:00:00','01/01/2011 13:30:00','seconds');	*/
function datediff(fromDate,toDate,interval) { 
	
	var second=1000, minute=second*60, hour=minute*60, day=hour*24, week=day*7; 
	fromDate = new Date(fromDate); 
	toDate = new Date(toDate); 
	var timediff = toDate - fromDate; 
	if (isNaN(timediff)) return NaN; 
	switch (interval) { 
		case "years": return toDate.getFullYear() - fromDate.getFullYear(); 
		case "months": return ( 
			( toDate.getFullYear() * 12 + toDate.getMonth() ) 
			- 
			( fromDate.getFullYear() * 12 + fromDate.getMonth() ) 
		); 
		case "weeks"  : return Math.floor(timediff / week); 
		case "days"   : return Math.floor(timediff / day);  
		case "hours"  : return Math.floor(timediff / hour);  
		case "minutes": return Math.floor(timediff / minute); 
		case "seconds": return Math.floor(timediff / second); 
		default: return undefined; 
	} 
}

