
// Variables globales
var g_bkey;		// Clé du jour
var pixel_factor = 60;
var detail_good = false;
var detail_bad = false;
var nb_jours = 6;
var deletedata;



//////////////////////////////
// COMPORTEMENTS PAR DÉFAUT //
//////////////////////////////
function start_home(){
	
	//localStorage.setItem("cond2016927",10);
	
	draw_home_page();
}
function switch_from(page){

	if ( page == "good" )		detail_good = !detail_good;
	else if ( page == "bad" )	detail_bad = !detail_bad;
	draw_home_page();
}
function switch_time(){
	
	if ( nb_jours == 6 ){
		nb_jours = 13;
		//detail_good = true;		detail_bad = true;
	}else if ( nb_jours == 13 ){
		nb_jours = 20;
	}else if ( nb_jours == 20 ){
		nb_jours = 6;
		//detail_good = false;		detail_bad = false;
	}
	
	draw_home_page();
}



  /////////////////
 // DESSIN HOME //
/////////////////
function draw_home_page(){
	
	// Crée clé du jour
	var dt = new Date();
	g_bkey = dt.getFullYear().toString() + dt.getMonth().toString() + dt.getDate().toString();
	
	// effacer les vieilles données
	if ( deletedata != g_bkey ){
		deletedata = g_bkey;
		delete_old_data();
	}
	
	// Dessin des 3 graphiques
	if ( detail_bad ) draw_bad_page(); else draw_home_bad();
	if ( detail_good ) draw_good_page(); else draw_home_good();
	draw_home_diff();
}

// Dessine un graphique en étoile des 4 compteurs "bad"
function draw_home_bad(){
	
	// resizeBy
	$("#graph_bad").animate({width:'550px',height:'450px'});
	$("#bad_inputs").animate({width:'280px',opacity:'1'});
	
	// récupère valeurs du jour
	var coca = getLocalStorageVal("coca" + g_bkey);
	var clope = getLocalStorageVal("clope" + g_bkey);
	var bedo = getLocalStorageVal("bedo" + g_bkey)*1.5;
	var junk = getLocalStorageVal("junk" + g_bkey);
	var dent = getLocalStorageVal("dent" + g_bkey);
	var bad = parseInt(junk) + parseFloat(bedo) +  parseInt(clope) + parseInt(coca);
	localStorage.setItem("bad"+g_bkey,bad);
	
	// preparation
	var c = document.getElementById("graph_bad");
	c.width = 550;
	c.height = 450;
	var ctx = c.getContext("2d");
	
	// Background
	ctx.fillStyle = "#300";
	ctx.fillRect(0, 0, 550, 450);
	
	// Dessin des axes
	ctx.strokeStyle = "#733";
	ctx.lineWidth = 1;
	ctx.beginPath();
	//repere vertical
	/*for ( i=125; i<=425; i+=50 ){
		ctx.moveTo(i,100-(Math.sin((i-75)/4.44*Math.PI/90)*75)+(Math.random()*2-1)*20);
		ctx.lineTo(i,350+Math.sin((i-75)/4.44*Math.PI/90)*75+(Math.random()*2-1)*20);
	}
	ctx.stroke();
	//repere horizontal
	ctx.beginPath();
	for ( i=75; i<=375; i+=50 ){
		ctx.moveTo(150-(Math.sin((i-25)/4.44*Math.PI/90)*75)+(Math.random()*2-1)*20,i);
		ctx.lineTo(400+(Math.sin((i-25)/4.44*Math.PI/90)*75)+(Math.random()*2-1)*20,i);
	}*/
	// Reperes "racine carrée"
	for ( i=1; i<10; i+=2 ){
		ctx.moveTo(275+60*Math.sqrt(i),125-(Math.cos(i/18*Math.PI)*80)+(Math.random()*2-1)*20);
		ctx.lineTo(275+60*Math.sqrt(i),324+(Math.cos(i/18*Math.PI)*80)+(Math.random()*2-1)*20);
		ctx.moveTo(275-60*Math.sqrt(i),125-(Math.cos(i/18*Math.PI)*80)+(Math.random()*2-1)*20);
		ctx.lineTo(275-60*Math.sqrt(i),324+(Math.cos(i/18*Math.PI)*80)+(Math.random()*2-1)*20);
		
		ctx.moveTo(174-(Math.cos(i/18*Math.PI)*80)+(Math.random()*2-1)*20,225+60*Math.sqrt(i));
		ctx.lineTo(374+(Math.cos(i/18*Math.PI)*80)+(Math.random()*2-1)*20,225+60*Math.sqrt(i));
		ctx.moveTo(174-(Math.cos(i/18*Math.PI)*80)+(Math.random()*2-1)*20,225-60*Math.sqrt(i));
		ctx.lineTo(374+(Math.cos(i/18*Math.PI)*80)+(Math.random()*2-1)*20,225-60*Math.sqrt(i));
	}
	ctx.stroke();
	// axe vertical
	ctx.strokeStyle = "#922";
	ctx.lineWidth = 2;
	ctx.beginPath();
	ctx.moveTo(275,25);
	ctx.lineTo(275,425);
	ctx.stroke();
	// axe horizontal
	ctx.beginPath();
	ctx.moveTo(75,225);
	ctx.lineTo(475,225);
	ctx.stroke();
	// noms des axes
	ctx.fillStyle = "#922";
	ctx.font="28px segoe ui";
	ctx.fillText("sucre",460,410);
	ctx.fillText("tabac",30,50);
	//ctx.fillText("bedo",30,410);
	ctx.fillText("gras",460,50);
	
	// Tracés des figures
	// coordonnées
	var ox=255, oy=225, moy_x=ox, moy_y=oy, max=0;
	var junk_x = Math.sqrt(junk) * pixel_factor;
	if ( junk_x > max ){ max = junk_x; moy_x = ox + junk_x/2; moy_y = oy - junk_x/2; }
	var coca_x = Math.sqrt(coca) * pixel_factor;
	if ( coca_x > max ){ max = coca_x; moy_x = ox + coca_x/2; moy_y = oy + coca_x/2; }
	var bedo_x = Math.sqrt(bedo) * pixel_factor;
	if ( bedo_x > max ){ max = bedo_x; moy_x = ox - bedo_x/2; moy_y = oy + bedo_x/2; }
	var clope_x = Math.sqrt(clope) * pixel_factor;
	if ( clope_x > max ){ moy_x = ox - clope_x/2; moy_y = oy - clope_x/2; }
	// formes
	ctx.fillStyle = "rgba(220,120,120,"+(0.4+bad*0.03)+")";
	ctx.fillRect(275,225-junk_x,junk_x,junk_x);
	ctx.fillRect(275,225,coca_x,coca_x);
	ctx.fillRect(275-bedo_x,225,bedo_x,bedo_x);
	ctx.fillRect(275-clope_x,225-clope_x,clope_x,clope_x);
	// valeurs axes
	ctx.fillStyle = "#300";
	ctx.font="22px segoe ui";
	
	// valeur bad
	if ( bad > 0 ){
		ctx.font="28px segoe ui";
		ctx.fillText(bad,moy_x+5,moy_y+10);
	}
}

// Dessine un graphique des compteurs "good"
function draw_home_good(){
	
	// Resize
	$("#graph_good").animate({width:'550px',height:'450px'});
	$("#good_inputs").animate({width:'340px',opacity:'1'});
			
	// Définit les boutons
	var btn_dodo = localStorage.getItem("dodo");
	if ( btn_dodo!="NaN" && btn_dodo!=null ){
		document.getElementById("GBdodo_start").style.opacity = 0.3;
		document.getElementById("GBdodo_stop").style.opacity = 1;
	}else{
		document.getElementById("GBdodo_start").style.opacity = 1;
		document.getElementById("GBdodo_stop").style.opacity = 0.3;
	}
	
	var btn_walk = localStorage.getItem("walk");
	if ( btn_walk!="NaN" && btn_walk!=null ){
		document.getElementById("GBwalk_start").style.opacity = 0.3;
		document.getElementById("GBwalk_stop").style.opacity = 1;
	}else{
		document.getElementById("GBwalk_start").style.opacity = 1;
		document.getElementById("GBwalk_stop").style.opacity = 0.3;
	}
	
	var btn_foot = localStorage.getItem("foot");
	if ( btn_foot!="NaN" && btn_foot!=null ){
		document.getElementById("GBfoot_start").style.opacity = 0.3;
		document.getElementById("GBfoot_stop").style.opacity = 1;
	}else{
		document.getElementById("GBfoot_start").style.opacity = 1;
		document.getElementById("GBfoot_stop").style.opacity = 0.3;
	}
	
	var btn_cond = localStorage.getItem("cond");
	if ( btn_cond!="NaN" && btn_cond!=null ){
		document.getElementById("GBcond_start").style.opacity = 0.3;
		document.getElementById("GBcond_stop").style.opacity = 1;
	}else{
		document.getElementById("GBcond_start").style.opacity = 1;
		document.getElementById("GBcond_stop").style.opacity = 0.3;
	}
	// Calcule score du jour (en prenant en compte les 2 derniers jours)
	var i, dodo, walk, foot, cond, dent, dt=new Date(), bkey, som, sport, tsom=new Array(), tsport=new Array(), tgood=new Array();
	dt.setDate(dt.getDate()-2);
	for ( i=0; i<3; i++ ){
		
		// récupère valeurs brutes du jour
		bkey = dt.getFullYear().toString() + dt.getMonth().toString() + dt.getDate().toString();
		dodo = getLocalStorageVal("dodo" + bkey);
		walk = getLocalStorageVal("walk" + bkey);
		foot = getLocalStorageVal("foot" + bkey);
		cond = getLocalStorageVal("cond" + bkey);
		dent = getLocalStorageVal("dent" + bkey);
		//calcul des valeurs intermediaires
		som = dodo/60 - 5;	// rab au dela de 5h, en h => 0~7
		if ( som < 0 ) som = 0;
		if ( som > 7 ) som = 7;
		sport = (parseInt(walk)/3+parseInt(cond)+parseInt(foot))/5;
		
		tsom[i] = som;
		tsport[i] = sport;
		tgood[i] = som + sport + parseInt(dent);
		dt.setDate(dt.getDate()+1);
	}
	// calcul du total "good"
	som = Math.round((tsom[0] + tsom[1]*3 +tsom[2]*6 )*10)/100 + parseInt(dent)/2;
	sport = Math.round((tsport[0] + tsport[1]*3 +tsport[2]*6 )*10)/100 + parseInt(dent)/2;
	var good = som + sport;
	localStorage.setItem("good"+g_bkey,good);
	
	// preparation
	var c = document.getElementById("graph_good");
	c.width = 550;
	c.height = 450;
	var ctx = c.getContext("2d");
	var ox=275,oy=175;
	
	// Background
	ctx.fillStyle = "#030";
	ctx.fillRect(0, 0, 550, 450);
	
	// Dessin des axes
	//repere vertical
	ctx.strokeStyle = "#373";
	ctx.lineWidth = 1;
	ctx.beginPath();
	for ( i=125; i<=425; i+=50 ){
		ctx.moveTo(i,120-(Math.sin((i-75)/4.44*Math.PI/90)*75)+(Math.random()*2-1)*20);
		ctx.lineTo(i,340+Math.sin((i-75)/4.44*Math.PI/90)*75+(Math.random()*2-1)*20);
	}
	//repere horizontal
	for ( i=75; i<=375; i+=50 ){
		ctx.moveTo(150-(Math.sin((i-25)/4.44*Math.PI/90)*75)+(Math.random()*2-1)*20,i);
		ctx.lineTo(400+(Math.sin((i-25)/4.44*Math.PI/90)*75)+(Math.random()*2-1)*20,i);
	}
	
	// Reperes "racine carrée"
	/*for ( i=1; i<10; i+=2 ){
		
		ctx.moveTo(275+60*Math.sqrt(i),125-(Math.cos(i/18*Math.PI)*80)+(Math.random()*2-1)*20);
		ctx.lineTo(275+60*Math.sqrt(i),324+(Math.cos(i/18*Math.PI)*80)+(Math.random()*2-1)*20);
		ctx.moveTo(275-60*Math.sqrt(i),125-(Math.cos(i/18*Math.PI)*80)+(Math.random()*2-1)*20);
		ctx.lineTo(275-60*Math.sqrt(i),324+(Math.cos(i/18*Math.PI)*80)+(Math.random()*2-1)*20);
		
		ctx.moveTo(174-(Math.cos(i/18*Math.PI)*80)+(Math.random()*2-1)*20,oy+60*Math.sqrt(i));
		ctx.lineTo(374+(Math.cos(i/18*Math.PI)*80)+(Math.random()*2-1)*20,oy+60*Math.sqrt(i));
		if ( i < 5 ){
			ctx.moveTo(174-(Math.cos(i/18*Math.PI)*80)+(Math.random()*2-1)*20,oy-60*Math.sqrt(i));
			ctx.lineTo(374+(Math.cos(i/18*Math.PI)*80)+(Math.random()*2-1)*20,oy-60*Math.sqrt(i));
		}
	}
	*/
	ctx.stroke();
	// axe vertical
	ctx.strokeStyle = "#292";
	ctx.lineWidth = 2;
	ctx.beginPath();
	ctx.moveTo(ox,45);
	ctx.lineTo(ox,405);
	// axe horizontal
	ctx.moveTo(25,oy);
	ctx.lineTo(475,oy);
	ctx.stroke()
	// noms des axes
	ctx.fillStyle = "#292";
	ctx.font="28px segoe ui";
	ctx.fillText("sommeil",ox-50,40);
	ctx.fillText("exercice",ox-45,425);
	
	// Tracés des figures
	// coordonnées
	var som_x = Math.sqrt(som/2)*pixel_factor;
	var sport_x = Math.sqrt(sport/2)*pixel_factor;
	// formes
	ctx.fillStyle = "rgba(120,220,120,"+(0.4+good*0.03)+")";
	ctx.fillRect(ox-som_x,oy-som_x,som_x*2,som_x);
	ctx.fillRect(ox-sport_x,oy,sport_x*2,sport_x);
	// valeur du total
	ctx.fillStyle = "#030";
	//ctx.fillText(Math.round(parseFloat(som)*10+parseFloat(sport)*10)/10,258,oy+(sport_x-som_x)/2+10);
	ctx.fillText(Math.round(parseFloat(good)*10)/10,258,oy+(sport_x-som_x)/2+10);
}

// Dessine un graphique du bilan "good - bad"
function draw_home_diff(){
	
	var canvas_height=450, canvas_width=850,
	marge_haut=75, marge_gauche=40,
	hauteur_graphe=300,
	grad_jour=(canvas_width-(marge_gauche*2))/nb_jours;
	var i,j,ox=marge_gauche,oy=marge_haut+hauteur_graphe/2;
	
	// preparation
	var c = document.getElementById("graph_diff");
	var ctx = c.getContext("2d");
	
	// Background
	ctx.fillStyle = "#404";
	var gradient = ctx.createLinearGradient(0,0,0,canvas_height);
	gradient.addColorStop(0,"#030");
	//gradient.addColorStop(0.5,"#330");
	gradient.addColorStop(1,"#300");
	ctx.fillStyle = gradient;     
	ctx.fillRect(0, 0, canvas_width, canvas_height);
	
	// Dessin des axes
	//repere vertical
	ctx.strokeStyle = "#772";
	ctx.lineWidth = 1;
	ctx.font="20px segoe ui";
	var dt = new Date();
	dt.setDate(dt.getDate()-nb_jours);
	ctx.beginPath();
	j=0;
	for ( i=ox; i<=canvas_width; i+=grad_jour ){
			ctx.moveTo(i,marge_haut+(Math.random()*2-1)*20);
			ctx.lineTo(i,canvas_height-marge_haut+(Math.random()*2-1)*20);
		if ( j % parseInt(nb_jours/6) == 0 ){
			ctx.fillStyle = "#292";
			ctx.fillText(getDayOfWeek(dt.getDay())+" "+dt.getDate()+"/"+(dt.getMonth()+1),i-40,20);
			ctx.fillStyle = "#922";
			ctx.fillText(getDayOfWeek(dt.getDay())+" "+dt.getDate()+"/"+(dt.getMonth()+1),i-40,440);
		}
		dt.setDate(dt.getDate()+1);
		j++;
	}
	ctx.stroke();
	//repere horizontal
	ctx.beginPath();
	for ( i=75; i<=375; i+=50 ){
		ctx.moveTo(40+(Math.random()*2-1)*20,i);
		ctx.lineTo(820+(Math.random()*2-1)*20,i);
	}
	ctx.stroke();
	// axe vertical
	ctx.strokeStyle = "#992";
	ctx.lineWidth = 2;
	ctx.beginPath();
	ctx.moveTo(ox,marge_haut/2);
	ctx.lineTo(ox,canvas_height-marge_haut/2);
	ctx.stroke();
	// axe horizontal
	ctx.beginPath();
	ctx.moveTo(10,oy);
	ctx.lineTo(820,oy);
	ctx.stroke();
	// Tracé de la figure
	ctx.font="24px segoe ui";
	ctx.lineWidth = 2;
	ctx.beginPath();
	ctx.moveTo(marge_gauche,oy);
	dt = new Date();
	dt.setDate(dt.getDate()-nb_jours);
	var b_key,bad,good,d = dt.getDay();
	var diff,old_diff,polygonPoints;
	var diff_x,diff_y,old_diff_x,old_diff_y;
	var a,b,c;
	// pour les 7 derniers jours
	for ( i=0; i<=nb_jours; i++ ){
		
		// calcul diff
		b_key = dt.getFullYear().toString() + dt.getMonth().toString() + dt.getDate().toString();
		bad = getLocalStorageVal("bad"+b_key);
		good = getLocalStorageVal("good"+b_key);
		diff = good - bad;
		diff_x = marge_gauche+grad_jour*i;
		diff_y = oy-diff*10;
		
		// fond "week end"
		ctx.fillStyle = 'rgba(255, 255, 200, 0.15)';
		//if ( d==(6-i) || d==(7-i)%7 ){
		if ( dt.getDay() == 6 || dt.getDay() == 0 ){
			ctx.fillRect(marge_gauche+i*grad_jour-grad_jour/2,1,grad_jour-1,canvas_height-1);
		}
		
		// remplissage
		ctx.beginPath();
		if ( i > 0 ){
			// bad - bad : polygone rouge
			if ( diff<=0 && old_diff<=0 ){
				ctx.fillStyle = 'rgba(150, 20, 0, 0.5)';
				polygonPoints = [[old_diff_x,oy],[old_diff_x,old_diff_y],[diff_x,diff_y],[diff_x,oy]];
				ctx.fillPolygon(polygonPoints);
				
			// good - good : polygone vert
			}else if ( diff>=0 && old_diff>=0 ){
				ctx.fillStyle = 'rgba(20, 150, 0, 0.5)';
				polygonPoints = [[old_diff_x,oy],[old_diff_x,old_diff_y],[diff_x,diff_y],[diff_x,oy]];
				ctx.fillPolygon(polygonPoints);
				
			// bad - good : poly rouge + poly vert
			}else if( old_diff<=0 && diff>=0 ){
				a = (old_diff_y-diff_y)/(diff_x-old_diff_x);
				b = a * (old_diff_x-marge_gauche) - (oy - old_diff_y);
				c = Math.round(b/a)+marge_gauche;
				polygonPoints = [[old_diff_x,oy],[old_diff_x,old_diff_y],[c,oy]];
				ctx.fillStyle = 'rgba(150, 20, 0, 0.5)';
				ctx.fillPolygon(polygonPoints);
				polygonPoints = [[diff_x,oy],[diff_x,diff_y],[c,oy]];
				ctx.fillStyle = 'rgba(20, 150, 0, 0.5)';
				ctx.beginPath();
				ctx.fillPolygon(polygonPoints);
				
			// good - bad : poly vert + poly rouge
			}else if( old_diff>=0 && diff<=0 ){
				a = (old_diff_y-diff_y)/(diff_x-old_diff_x);
				b = a * (old_diff_x-marge_gauche) - (oy - old_diff_y);
				c = Math.round(b/a)+marge_gauche;
				polygonPoints = [[old_diff_x,oy],[old_diff_x,old_diff_y],[c,oy]];
				ctx.fillStyle = 'rgba(20, 150, 0, 0.5)';
				ctx.fillPolygon(polygonPoints);
				polygonPoints = [[diff_x,oy],[diff_x,diff_y],[c,oy]];
				ctx.fillStyle = 'rgba(150, 20, 0, 0.5)';
				ctx.beginPath();
				ctx.fillPolygon(polygonPoints);
				
			// cas qui ne devrait pas se présenter : au cas où, polygone gris
			}else{
				ctx.fillStyle = 'rgba(50, 50, 50, 0.5)';
				polygonPoints = [[old_diff_x,oy],[old_diff_x,old_diff_y],[diff_x,diff_y],[diff_x,oy]];
				ctx.fillPolygon(polygonPoints);
			}
		}
		
		// tracé principal
		ctx.strokeStyle = "rgba(180,180,50,0.3)";
		ctx.beginPath();
		ctx.moveTo(old_diff_x,old_diff_y);
		ctx.lineTo(diff_x,diff_y);
		ctx.stroke();
		
		// valeur 
		if ( diff >= 0 ){
			ctx.fillStyle = "#392";
			ctx.fillText(Math.round(diff),diff_x-10,diff_y-5,40);
		}else{
			ctx.fillStyle = "#932";
			ctx.fillText(Math.round(diff),diff_x-10,diff_y+25,40);
		}
		
		// jour suivant
		dt.setDate(dt.getDate()+1);
		old_diff = diff;
		old_diff_x = diff_x;
		old_diff_y = diff_y;
	}
}




////////////////////////////////////////////
////////////	 EVENEMENTS		////////////
////////////////////////////////////////////

// Incremente un compteur (bouton +1)
function incremente ( cpt_name ){
	// crée une clé représentant la journée (YYYYMMDD)
	var dt = new Date();
	g_bkey = dt.getFullYear().toString() + dt.getMonth().toString() + dt.getDate().toString();
	// Crée nom de variable : [nom_compteur][cle_jour]
	var key = cpt_name + g_bkey;
	// Récupère valeur actuelle
	var value = getLocalStorageVal(key);
	// Incrémente
	value++;
	// Enregistre nouvelle valeur du compteur du jour
	localStorage.setItem(key,value);
	// Met a jour graphique
	draw_home_page();
}

// memorise datetime actuel (bouton START)
function start_count ( cpt_name ){
	var dt = new Date();
	var value = dt.toString();
	localStorage.setItem(cpt_name,value);
	draw_home_page();
}

// calcule une durée (bouton STOP)
function stop_count ( cpt_name ){
	
	// verifie le compteur
	var start = getLocalStorageVal(cpt_name);
	if ( start != 0 ){
		
		// calcule diff entre maintenant et date "start" enregistrée
		var dt = new Date();
		var end = dt.toString();
		var span = datediff(start, end, "minutes");
		// crée clé du jour
		var dt = new Date();
		g_bkey = dt.getFullYear().toString() + dt.getMonth().toString() + dt.getDate().toString();
		var key = cpt_name + g_bkey;
		// Récupère valeur actuelle
		var value = localStorage.getItem(key);
		// Ajoute nouvelle durée
		if ( value == "NaN" || value==null )	value = span;
		else									value = parseInt(value) + span;
		// enregistrer nouvelle durée
		localStorage.setItem(key,value);
		// liberer marqueur
		localStorage.removeItem(cpt_name);
		draw_home_page();
	}
}





  /////////////////
 // DETAIL BAD  //
/////////////////
function draw_bad_page(){
	
	// Resize
	$("#graph_bad").animate({width:'850px'});
	$("#bad_inputs").animate({width:'10px',opacity:'0'});
			
	var dt = new Date();
	var val,b_key,key,bad;
	var coca,junk,bedo,clope,dent;
	var noms = new Array("sucre","tabac","???","gras","BAD");
	var canvas_height=450, canvas_width=850,
	marge_haut=10, marge_gauche=40,
	hauteur_graphe=380, pxpp=hauteur_graphe/20,
	grad_jour=(canvas_width-(marge_gauche*2))/nb_jours;
	var i,j,ox=marge_gauche,oy=marge_haut+hauteur_graphe;
	
	// preparation
	var c = document.getElementById("graph_bad");
	c.height = canvas_height;
	c.width = canvas_width;
	var ctx = c.getContext("2d");
	
	// Background
	ctx.fillStyle = "#300";
	ctx.fillRect(0, 0, canvas_width, canvas_height);
	
	// Dessin des axes et repères pour les 5 graphiques
	ctx.strokeStyle = "#733";
	ctx.font="20px segoe ui";
	var dt = new Date();
	dt.setDate(dt.getDate()-nb_jours);
	ctx.beginPath();
	j = 0;
	for ( i=marge_gauche; i<=canvas_width; i+=grad_jour ){
		
		// marquer le week end
		if ( dt.getDay() == 6 || dt.getDay() == 0 ){
			ctx.fillStyle = "rgba(255, 200, 200, 0.15)";
			ctx.fillRect(i-(grad_jour/2),1,grad_jour-1,canvas_height-1);
		}
		// reperes verticaux
		ctx.fillStyle = "#b22";
		ctx.moveTo(i,marge_haut+(Math.random()*2-1)*20);
		ctx.lineTo(i,canvas_height-marge_haut+(Math.random()*2-1)*20);
		if ( j % parseInt(nb_jours/6) == 0 )
		ctx.fillText(getDayOfWeek(dt.getDay())+" "+dt.getDate()+"/"+(dt.getMonth()+1),i-40,440);
		
		// jour suivant
		dt.setDate(dt.getDate()+1);
		j++;
	}
	ctx.stroke();
	
	// reperes horizontaux
	ctx.beginPath();
	for ( j=2; j<20; j+=2 ){
		ctx.moveTo(ox,oy-pxpp*j);
		ctx.lineTo(canvas_width,oy-pxpp*j);
	
	}
	ctx.stroke();
	// axe vertical
	ctx.beginPath();
	ctx.strokeStyle = "#922";
	ctx.lineWidth = 2;
	ctx.beginPath();
	ctx.moveTo(ox,marge_haut/2);
	ctx.lineTo(ox,oy+marge_haut/2);
	ctx.stroke();
	// axe horizontal
	ctx.fillStyle = "#b22";
	ctx.font="24px segoe ui";
	ctx.beginPath();
	ctx.moveTo(10,oy);
	ctx.lineTo(820,oy);
	ctx.stroke();
	
	// Tracé des courbes
	ctx.strokeStyle = "#b22";
	ctx.lineWidth = 2;
	var old_x,x,old_coca=-1,old_clope=-1,old_bedo=-1,old_junk=-1;
	var old_y1,y1,old_y2,y2,old_y3,y3,old_y4,y4;
	dt = new Date();
	dt.setDate(dt.getDate()-nb_jours);
	var polygon_coca = [[ox+nb_jours*grad_jour,oy],[ox,oy],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,]];
	var polygon_clope = [[ox+nb_jours*grad_jour,oy],[ox,oy],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,]];
	var polygon_bedo = [[ox+nb_jours*grad_jour,oy],[ox,oy],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,]];
	var polygon_junk = [[ox+nb_jours*grad_jour,oy],[ox,oy],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,]];
	for ( i=0; i<=nb_jours; i++ ){
		
		// get values
		b_key = dt.getFullYear().toString() + dt.getMonth().toString() + dt.getDate().toString();
		coca = getLocalStorageVal("coca"+b_key);	//if ( coca > 10 ) coca = 10;
		clope = getLocalStorageVal("clope"+b_key);	//if ( clope > 10 ) clope = 10;
		bedo = getLocalStorageVal("bedo"+b_key)*1.5;	//if ( bedo > 10 ) bedo = 10;
		junk = getLocalStorageVal("junk"+b_key);	//if ( junk > 10 ) junk = 10;
		bad = getLocalStorageVal("bad"+b_key);		//if ( bad > 20 ) bad = 20;
		
		// tracé avec point précédent
		x = ox + grad_jour*i;
		y1 = oy - pxpp* parseInt(coca);
		y2 = oy - pxpp*(parseInt(coca)+parseInt(clope));
		y3 = oy - pxpp*(parseInt(coca)+parseInt(clope)+parseFloat(bedo));
		y4 = oy - pxpp*(parseInt(coca)+parseInt(clope)+parseFloat(bedo)+parseInt(junk));
		if ( i > 0 ){
			ctx.beginPath();
			ctx.moveTo ( old_x, old_y1 );
			ctx.lineTo ( x, y1 );
			polygon_coca[2+i][0] = x;
			polygon_coca[2+i][1] = y1;
			
			ctx.moveTo ( old_x, old_y2 );
			ctx.lineTo ( x, y2 );
			polygon_clope[2+i][0] = x;
			polygon_clope[2+i][1] = y2;
			
			ctx.moveTo ( old_x, old_y3 );
			ctx.lineTo ( x, y3 );
			polygon_bedo[2+i][0] = x;
			polygon_bedo[2+i][1] = y3;
			
			ctx.moveTo ( old_x, old_y4 );
			ctx.lineTo ( x, y4 );
			polygon_junk[2+i][0] = x;
			polygon_junk[2+i][1] = y4;
			
			ctx.fillStyle = "#932";
			ctx.fillText(Math.round(bad),x-10,y4-15,40);
			ctx.stroke();
		}else{
			
			polygon_coca[2][0] = x;
			polygon_coca[2][1] = y1;
			
			polygon_clope[2][0] = x;
			polygon_clope[2][1] = y2;
			
			polygon_bedo[2][0] = x;
			polygon_bedo[2][1] = y3;
			
			polygon_junk[2][0] = x;
			polygon_junk[2][1] = y4;
		}
		old_x = x;
		old_y1 = y1;
		old_y2 = y2;
		old_y3 = y3;
		old_y4 = y4;
		dt.setDate(dt.getDate()+1);
	}
	
	ctx.fillStyle = 'rgba(150, 0, 0, 0.25)';
	ctx.beginPath();
	ctx.fillPolygon(polygon_coca);
	ctx.beginPath();
	ctx.fillPolygon(polygon_clope);
	ctx.beginPath();
	ctx.fillPolygon(polygon_bedo);
	ctx.beginPath();
	ctx.fillPolygon(polygon_junk);
}




  //////////////////
 // DETAIL GOOD  //
//////////////////
function draw_good_page(){
	
	// Resize
	$("#graph_good").animate({width:'850px'});
	$("#good_inputs").animate({width:'22px',opacity:'0'});
			
	var dt = new Date();
	var val,b_key,key,good;
	var dodo,walk,foot,cond,dent;
	var canvas_height=450, canvas_width=850,
	marge_haut=50, marge_gauche=40,
	hauteur_graphe=380, pxpp=hauteur_graphe/20,
	grad_jour=(canvas_width-(marge_gauche*2))/nb_jours;
	var i,j,ox=marge_gauche, oy=marge_haut+hauteur_graphe;
	
	// preparation
	var c = document.getElementById("graph_good");
	c.height = canvas_height;
	c.width = canvas_width;
	var ctx = c.getContext("2d");
	
	// Background
	ctx.fillStyle = "#030";
	ctx.fillRect(0, 0, canvas_width, canvas_height);
	
	// Dessin des axes et repères
	ctx.strokeStyle = "#373";
	ctx.lineWidth = 1;
	ctx.font="20px segoe ui";
	var dt = new Date();
	dt.setDate(dt.getDate()-nb_jours);
	ctx.beginPath();
	j = 0;
	for ( i=marge_gauche; i<=canvas_width; i+=grad_jour ){
		
		// marquer le week end
		if ( dt.getDay() == 6 || dt.getDay() == 0 ){
			ctx.fillStyle = "rgba(200, 255, 200, 0.15)";
			ctx.fillRect(i-(grad_jour/2),1,grad_jour-1,canvas_height-1);
		}
		// reperes verticaux
		ctx.fillStyle = "#2b2";
		ctx.moveTo(i,marge_haut+(Math.random()*2-1)*20);
		ctx.lineTo(i,oy+(Math.random()*2-1)*20);
		if ( j % parseInt(nb_jours/6) == 0 )
			ctx.fillText(getDayOfWeek(dt.getDay())+" "+dt.getDate()+"/"+(dt.getMonth()+1),i-40,20);
		
		// jour suivant
		dt.setDate(dt.getDate()+1);
		j++;
	}
	ctx.stroke();
	
	// reperes horizontaux
	ctx.beginPath();
	for ( j=0; j<20; j+=2 ){
		ctx.moveTo(marge_gauche,oy-pxpp*j);
		ctx.lineTo(canvas_width,oy-pxpp*j);
	}
	ctx.stroke();
	// axe vertical
	ctx.beginPath();
	ctx.strokeStyle = "#292";
	ctx.lineWidth = 2;
	ctx.beginPath();
	ctx.moveTo(marge_gauche,marge_haut);
	ctx.lineTo(marge_gauche,oy);
	ctx.stroke();
	// axe horizontal
	//ctx.fillStyle = "#2b2";
	ctx.font="24px segoe ui";
	ctx.beginPath();
	ctx.moveTo(10,oy);
	ctx.lineTo(820,oy);
	ctx.stroke();
	
	// Tracé des courbes
	// pour les 30 derniers jours
	ctx.strokeStyle = "#2b2";
	ctx.lineWidth = 2;
	var som, sport, tsom=new Array(), tsport=new Array();
	var old_x, x, old_y1, y1, old_y2, y2, old_y3, y3;
	dt = new Date();
	dt.setDate(dt.getDate()-nb_jours-2);
	y = marge_haut + hauteur_graphe;
	var polygon_som = [[marge_gauche+nb_jours*grad_jour,y],[marge_gauche,y],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,]];
	var polygon_sport = [[marge_gauche+nb_jours*grad_jour,y],[marge_gauche,y],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,]];
	var polygon_dent = [[marge_gauche+nb_jours*grad_jour,y],[marge_gauche,y],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,],[,]];
	for ( i=0; i<=nb_jours+2; i++ ){
		
		// get values
		b_key = dt.getFullYear().toString() + dt.getMonth().toString() + dt.getDate().toString();
		dodo = getLocalStorageVal("dodo"+b_key);
		som = dodo/60 - 5;
		if ( som < 0 ) som = 0;
		if ( som > 7 ) som = 7;
		tsom[i] = som;
		cond = getLocalStorageVal("cond"+b_key);
		foot = getLocalStorageVal("foot"+b_key);
		walk = getLocalStorageVal("walk"+b_key);
		sport = (parseInt(walk)/3+parseInt(cond)+parseInt(foot))/5;
		if ( sport > 14 ) sport = 14;
		tsport[i] = sport;
		dent = parseInt(getLocalStorageVal("dent"+b_key));
		
		// tracé avec point précédent
		x = marge_gauche + grad_jour*(i-2);
		if ( i == 2 ){
			som = (tsom[i-2]+tsom[i-1]*3+tsom[i]*6)/10;
			y1 = oy-(pxpp*som);
			sport = (tsport[i-2]+tsport[i-1]*3+tsport[i]*6)/10;
			y2 = y1-(pxpp*sport);
			y3 = y2-(pxpp*dent);
			polygon_som[2][0] = x;
			polygon_som[2][1] = y1;
			polygon_sport[2][0] = x;
			polygon_sport[2][1] = y2;
			polygon_dent[2][0] = x;
			polygon_dent[2][1] = y3;
		
		}else if ( i > 2 ){
			som = (tsom[i-2]+tsom[i-1]*3+tsom[i]*6)/10;
			y1 = oy-(pxpp*som);
			sport = (tsport[i-2]+tsport[i-1]*3+tsport[i]*6)/10;
			y2 = y1-(pxpp*sport);
			y3 = y2-(pxpp*dent);
			ctx.beginPath();
			ctx.moveTo ( old_x, old_y1 );
			ctx.lineTo ( x, y1 );
			polygon_som[i][0] = x;
			polygon_som[i][1] = y1;
			
			ctx.moveTo ( old_x, old_y2 );
			ctx.lineTo ( x, y2 );
			polygon_sport[i][0] = x;
			polygon_sport[i][1] = y2;
			
			ctx.moveTo ( old_x, old_y3 );
			ctx.lineTo ( x, y3 );
			polygon_dent[i][0] = x;
			polygon_dent[i][1] = y3;
			
			ctx.fillStyle = "#392";
			ctx.fillText(Math.round(som+sport+dent),x-10,y3-15,40);
			
			ctx.stroke();
		}else{
			
		}
		old_x = x;
		old_y1 = y1;
		old_y2 = y2;
		old_y3 = y3;
		dt.setDate(dt.getDate()+1);
	}
	
	ctx.fillStyle = 'rgba(0, 150, 0, 0.45)';
	ctx.beginPath();
	ctx.fillPolygon(polygon_som);
	ctx.beginPath();
	ctx.fillPolygon(polygon_sport);
	ctx.beginPath();
	ctx.fillPolygon(polygon_dent);
}




///////////////////////////
////////	MISC	///////
///////////////////////////

// Supprime les données vieilles de plus de 21 jours
function delete_old_data(){
	var dt = new Date();
	dt.setDate(dt.getDate()-21);
	var bkey = dt.getFullYear().toString() + dt.getMonth().toString() + dt.getDate().toString();
	localStorage.removeItem("dodo"+bkey);
	localStorage.removeItem("walk"+bkey);
	localStorage.removeItem("cond"+bkey);
	localStorage.removeItem("foot"+bkey);
	localStorage.removeItem("dent"+bkey);
	localStorage.removeItem("good"+bkey);
	localStorage.removeItem("coca"+bkey);
	localStorage.removeItem("clope"+bkey);
	localStorage.removeItem("bedo"+bkey);
	localStorage.removeItem("junk"+bkey);
	localStorage.removeItem("bad"+bkey);
	console.log("DELETE "+bkey);
}

//create and fill polygon
CanvasRenderingContext2D.prototype.fillPolygon = function (pointsArray, fillColor) {
    if (pointsArray.length <= 0) return;
    this.moveTo(pointsArray[0][0], pointsArray[0][1]);
    for (var i = 0; i < pointsArray.length; i++) {
        this.lineTo(pointsArray[i][0], pointsArray[i][1]);
    }
	this.fill();
}
// Recupere une valeur a partir de sa clé dans le localStorage
function getLocalStorageVal ( key ){
	var val = localStorage.getItem(key);
	if ( val == "NaN" || val == null ) return 0;
	else return val;
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


