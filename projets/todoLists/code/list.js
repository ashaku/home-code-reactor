
// Variables globales
var listes;
var active_list;

start();

// Démarrage de la page
function start(){
	console.log("start()");
	
	// Recuperer et afficher les listes du localStorage
	var str_listes = localStorage.getItem("my_lists");
	if ( str_listes == null || str_listes == "undefined" )
		init_lists();
	else{
		listes = JSON.parse(str_listes);
		active_list = 0;
	}
	display_lists();
	change_color();
}

// Si localStorage vide => créer une structure JSON vide
function init_lists(){
	console.log("init_lists()");
	listes = {"lists":[]};
	localStorage.setItem("my_lists",JSON.stringify(listes));
	console.log("UPDATE JSON : "+JSON.stringify(listes));
}

// Affiche les listes existantes dans le localStorage
function display_lists(){
	console.log("display_lists()");
	var i, nb_listes=listes.lists.length;
	
	// Effacer toutes les listes
	$("#lists").html("");
	
	// Afficher toutes les listes
	if ( nb_listes > 0 ){
		for ( i=0; i<nb_listes; i++ )	display_list(i);
	}
}

// Afficher une liste
function display_list(list_id){
	var list = listes.lists[list_id];
	
	// Créer HTML de la liste
	var html = "<div id='list"+list_id+"' class='list'><div class='list_title' onclick='deploy_list("+list_id+");' style='box-shadow:0px 0px 16px 12px "+list.color+";background-color:"+list.color+";'>"+list.name+"</div><div class='list_items' ";
	if ( list_id == active_list )	html += "style='height:100%'"; else	html += "style='height:0px;'";
	html += ">";
	var j,d;
	for ( j=0; j<list.items.length; j++){
		d = new Date(list.items[j].date_due);
		html += "<div id='list"+list_id+"item"+j+"' class='list_item' onclick='remove_item("+list_id+","+j+");'><span class='item_name'>"+list.items[j].item_name+"</span><span class='item_date'>"+getDayOfWeek(d.getDay())+" "+d.getDate()+"/"+parseInt(d.getMonth()+1)+"</span></div>";
	}
	d = new Date();
	d.setDate(d.getDate()+15);
	html += "<div class='list_item'><input type='text' name='new_item"+list_id+"' id='new_item"+list_id+"' size='32' placeholder='nouvel item' onKeyPress='handle_item_key(event,"+list_id+")'/><input type='text' name='new_item"+list_id+"_date' id='new_item"+list_id+"_date' size='10' value='"+d.getFullYear()+"-"+parseInt(d.getMonth()+1)+"-"+d.getDate()+"' style='font-size:18px;color:#666'/><img src='add-128.png' width='40' onclick='add_new_item("+list_id+");' style='float:right'/></div></div></div>";
	console.log("display_list("+list_id+") : "+list.name);
	$("#lists").append(html);
}

// Ajouter une nouvelle liste
function add_new_list(){
	var list_name = $("#new_list_name").val();
	console.log("add_new_list('"+list_name+"')");
	
	// Ajouter la nouvelle liste au JSON
	var color = $("#new_list_color").val();
	var new_list = {"color":color,"name":list_name,"items":[]};
	listes.lists.push(new_list);
	localStorage.setItem("my_lists",JSON.stringify(listes));
	console.log("UPDATE JSON : "+JSON.stringify(listes));
	
	// Raffraichir l'affichage en donnat le focus à la nouvelle liste
	display_lists();				
	$("#new_list_name").val("");
	deploy_list(listes.lists.length-1);
}


// Ajouter un nouvel item à une liste
function add_new_item(list_id){
	var item_text = $("#new_item"+list_id).val();
	var item_date = $("#new_item"+list_id+"_date").val();
	console.log("add_new_item("+list_id+") : "+item_text+" "+item_date);
	
	// Ajouter le nouvel item au JSON de la liste
	var d = new Date();
	var str_date = d.getFullYear()+"-"+parseInt(d.getMonth()+1)+"-"+d.getDate();
	var new_item = {"item_name":item_text,"date_creation":str_date,"date_due":item_date};
	listes.lists[list_id].items.push(new_item);
	localStorage.setItem("my_lists",JSON.stringify(listes));
	console.log("UPDATE JSON : "+JSON.stringify(listes));
	
	display_lists();
}

// Supprimer un item d'une liste
function remove_item(list_id,item_id){
	console.log("remove_item("+list_id+","+item_id+")");
	
	// Si dernier item de la liste => supprimer la liste
	if ( listes.lists[list_id].items.length == 1 ) delete_list(list_id);
	else{
		
		// Reconstruire le tableau d'item dans le JSON
		var i,temp_list_items = [];
		for ( i=0; i<listes.lists[list_id].items.length; i++){
			if ( i != item_id )
			temp_list_items.push(listes.lists[list_id].items[i]);
		}
		listes.lists[list_id].items = temp_list_items;
	
		localStorage.setItem("my_lists",JSON.stringify(listes));
		console.log("UPDATE JSON : "+JSON.stringify(listes));
		display_lists();
	}
}

// Supprimer une liste
function delete_list(list_id){
	console.log("delete_list("+list_id+")");
	
	// Reconstruire le tableau des listes dans le JSON
	var i,temp_lists = [];
	for ( i=0; i<listes.lists.length; i++){
		if ( i != list_id )
		temp_lists.push(listes.lists[i]);
	}
	listes.lists = temp_lists;
	localStorage.setItem("my_lists",JSON.stringify(listes));
	console.log("UPDATE JSON : "+JSON.stringify(listes));
	display_lists();
	if ( listes.lists.length > 0 ) deploy_list(0);
}

// Deployer une liste (et replier toutes les autres)
function deploy_list(list_id){
	console.log("deploy_list("+list_id+")");
	//var height = $("#list"+active_list+" .list_items").height();
	var nb = listes.lists[list_id].items.length;
	var height = 60 + 60*nb;
	$("#list"+active_list+" .list_items").animate({height:"0px"});
	$("#list"+list_id+" .list_items").animate({height:height+"px"}).animate({height:"100%"});
	active_list = list_id;
}

// Met à jour le colorPicker
function change_color(){
	var c = $("#new_list_color").val();
	$("#new_list_color").css("backgroundColor",c);
	console.log("change_color() : "+c);
}



// Validation des item sur appui de la touche "entrée"
function handle_list_key(e){
    if ( e.wich == 13 || e.keyCode == 13 )    add_new_list();
    return false;
}
function handle_item_key(e,list_id){
    if ( e.wich == 13 || e.keyCode == 13 )    add_new_item(list_id);
    return false;
}



// jour lisible de la semaine
function getDayOfWeek( d ){
	switch(d){
		case 0 : return "dim";
		case 1 : return "lun";
		case 2 : return "mar";
		case 3 : return "mer";
		case 4 : return "jeu";
		case 5 : return "ven";
		case 6 : return "sam";
	}
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
