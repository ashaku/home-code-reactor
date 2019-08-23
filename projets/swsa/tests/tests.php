<?
	// LIBRAIRIE DES TESTS



	//////////////////////////////////////////////
	///////	RUNES, RUNAGE & CODE GENETIQUE	//////
	//////////////////////////////////////////////
	
	//Créé une rune 2 Energy / SPD+39 / CR+% CD+% PRE+% RES+% CR+% CD+% et vérifie que le code fourni est bien décodé
	function tu_rune_decode(){
		
		// Créer rune
		$position = 2;
		$family = RUNEFAMILY::Energy;
		$mainStat = STATS::SPD;
		$code = "100101110111100101";
		$r = new Rune($position,$family,$mainStat,$code);
		
		// Verifier modifiers
		$defBonus = $r->get_defaultBonus();
		foreach ( $r->get_statsModifiers() as $statIndex=>$value ){
			switch ($statIndex){
				case 6 : if ( $value != 39 ) return array(false,"modificateur SPD incorrect ($value au lieu de 39)");	break;
				case 9 : if ( $value != 2*$defBonus ) return array(false,"modificateur CR incorrect ($value au lieu de ".(2*$defBonus).")");	break;
				case 11 : if ( $value != 2*$defBonus ) return array(false,"modificateur CD incorrect ($value au lieu de ".(2*$defBonus).")");	break;
				case 13 : if ( $value != $defBonus ) return array(false,"modificateur PRE incorrect ($value au lieu de ".$defBonus.")");	break;
				case 15 : if ( $value != $defBonus ) return array(false,"modificateur RES incorrect ($value au lieu de ".$defBonus.")");	break;
				default : if ( $value != 0 ) return array(false,"modificateur ".$statIndex." incorrect ($value au lieu de 0)");	break;
			}
		}
		
		// Verifier main
		$m = $r->get_mainStat();
		$ms = STATS::get_short_name($m->get_stat());
		if ( $ms != "SPD" )	return array(false,"stat main incorrecte ($ms au lieu de SPD)");
		$mt = $m->get_type();
		if ( $mt != "0" )	return array(false,"type main incorrect ($mt au lieu de 0)");
		$mv = $m->get_value();
		if ( $mv != 39 )	return array(false,"valeur main incorrecte ($mv au lieu de 39)");
		
		// Verifier sub
		$s = $r->get_subStats();
		$b = $r->get_defaultBonus();
		$i = 4;
		foreach ( $s as $sub ){
			if ( $i>7 ) $i=4;
			$ms = $sub->get_stat();
			if ( $ms != $i )	return array(false,"stat sub ".($i-3)." incorrecte ($ms au lieu de $i)");
			$mt = $sub->get_type();
			if ( $mt != "1" )	return array(false,"type sub ".($i-3)." incorrect ($mt au lieu de 1)");
			$mv = $sub->get_value();
			if ( $mv != $b )	return array(false,"valeur sub ".($i-3)." incorrect ($mv au lieu de $b)");
			$i++;
		}
		
		// Vérifier code binaire
		$c = $r->get_code();
		if ( $c != $code )	return array(false,"Erreur decode : code binaire modifié ($c au lieu de $code)");
		
		return array(true,"OK");
	}
	
	//Créé une rune avec 2 sub identiques au main et vérifie leur correction
	function tu_rune_correct(){
		// Créer une rune avec un sub en double
		// Energy Main:PV+51% Sub:PV+% ATK+% ATK+% DEF+% SPD+ HP+%
		$position = 2;
		$family = RUNEFAMILY::Energy;
		$mainStat = STATS::HP;
		$code = "000001001010011000";
		$r = new Rune($position,$family,$mainStat,$code);
		
		// Verifie que les stat en erreur a changé
		$s = $r->get_subStats();
		if ( $s[0]->get_stat() == STATS::HP )	return array(false,"La sub 1 en doublon du main est inchangée");
		if ( $s[5]->get_stat() == STATS::HP )	return array(false,"La sub 6 en doublon du main est inchangée");
		
		// Verifie que la main stat n est pas comptée en double
		$mods = $r->get_statsModifiers();
		if ( $mods[1] != 51 )	return array(false,"La main stat a été comptée en double dans les modificateurs : ".$mods[1]." au lieu de 51");
		
		// Verifie que le code binaire de la rune a changé
		$newRuneCode = $r->get_code();
		if ( $newRuneCode == $code )	return array(false,"Le code binaire est inchangé");
		
		return array(true,"OK");
	}
	
	//Créé deux runes avec toutes les stats possibles en sub et vérifie les modificateurs (somme de tous les bonus) 
	function tu_rune_modifiers(){
		
		// Rune 1 : Main:Atk+99 Sub:HP+% ATK+% DEF+% SPD+ CD+% RES+%
		$position = 1;
		$family = RUNEFAMILY::Energy;
		$code = "000001010011101111";
		$r = new Rune($position,$family,-1,$code);
		$mods = $r->get_statsModifiers();
		$b = $r->get_defaultBonus();
		foreach ( $mods as $modIndex=>$value ){
			switch ($modIndex ){
				case 2 :	if ( $value != 99 )	return array(false,"modificateur $modIndex incorrect : ".$value." au lieu de 99");	break;
				case 1 :	case 3 :		case 5 :	case 6 :	case 11 :	case 15:	
							if ( $value != $b )	return array(false,"modificateur $modIndex incorrect : ".$value." au lieu de $b");	break;
				default :	if ( $value != 0 )	return array(false,"modificateur $modIndex incorrect : ".$value." au lieu de 0");	break;
			}
		}
		
		return array(true,"OK");
	}
	
	
	//Créé 6 runes à partir d un set prédéfini et vérifie les modificateurs (famille + bonus)
	function tf_runage(){
		$mains = "11011011";	// SPD CR PRE
		$families = "0000000100000";	// 4 fatale 2 energy
		$rune1 = "100101110111100101";	// CR CD Pre Res CR CD
		$rune2 = "100101110111100101";	// CR CD Pre Res CR CD
		$rune3 = "000001010011000001";	// HP Atq Def Vit HP Atq
		$rune4 = "000001010011000001";	// HP Atq Def Vit HP Atq
		$rune5 = "100101110111100101";	// CR CD Pre Res CR CD
		$rune6 = "000001010011000001";	// HP Atq Def Vit HP Atq
		$runageCode = $mains.$families.$rune1.$rune2.$rune3.$rune4.$rune5.$rune6;
		$runage = new Runage($runageCode);
		
		// Calculer chiffres attendus
		$r=new Rune(1,0,0,"");
		$defVal = $r->get_defaultBonus();
		$expectedModifiers = array(1530,$defVal*6,99,$defVal*6,99,$defVal*3,39+$defVal*3,0,0,$defVal*6,0,65+$defVal*6,0,51+$defVal*3,0,$defVal*3);
		$statMod = $runage->get_stats_modifiers();
		for ( $i=0; $i<16; $i++ ){
			if ( $statMod[$i] != $expectedModifiers[$i] )	return array(false,"Erreur sub, modificateur $i : ".$statMod[$i]." au lieu de ".$expectedModifiers[$i]);
		}
		
		$expectedModifiers = array(0,15,0,35,0,0,0,0,0,0,0,0,0,0,0,0);
		$statMod = $runage->get_family_modifiers();
		for ( $i=0; $i<16; $i++ ){
			if ( $statMod[$i] != $expectedModifiers[$i] )	return array(false,"Erreur family, modificateur $i : ".$statMod[$i]." au lieu de ".$expectedModifiers[$i]);
		}
		
		return array(true,"OK");
	}
	
	
	
	
	
	//////////////////////////////////////////////
	////////////	MONSTRE		//////////////////
	//////////////////////////////////////////////
	
	//Créé un monstre avec son runage et vérifie ses stats avec runes et leader skill
	function tu_monstre_stats(){
		
		$name = "Veromos";
		$mains = "11011011";	// SPD CR PRE
		$families = "0000000100000";	// 4 fatale 2 energy
		$rune1 = "100101110111100101";	// CR CD Pre Res CR CD
		$rune2 = "100101110111100101";	// CR CD Pre Res CR CD
		$rune3 = "000001010011000001";	// HP Atq Def Vit HP Atq
		$rune4 = "000001010011000001";	// HP Atq Def Vit HP Atq
		$rune5 = "100101110111100101";	// CR CD Pre Res CR CD
		$rune6 = "000001010011000001";	// HP Atq Def Vit HP Atq
		$runeCode = $mains.$families.$rune1.$rune2.$rune3.$rune4.$rune5.$rune6;
		$monster = MonsterLib::load_monster($name,$runeCode);
		$monStats = $monster->get_base_stats();
		
		// RuneStats
		$r=new Rune(1,0,0,"");
		$defVal = $r->get_defaultBonus();
		$hpRune = 1531 + round($monStats[0]*($defVal*6+15)/100);
		$expectedModifiers = array(	$hpRune,
									99 + round($monStats[1]*($defVal*6+35)/100),
									99 + round($monStats[2]*$defVal*3/100),
									39 + round($monStats[3]*$defVal*3/100),
									$defVal*6,
									65 + $defVal*6,
									51 + $defVal*3,
									$defVal*3);
		$statMod = $monster->get_runes_mod();
		for ( $i=0; $i<8; $i++ ){
			if ( $statMod[$i] != $expectedModifiers[$i] )	return array(false,"Erreur modificateur $i : ".$statMod[$i]." au lieu de ".$expectedModifiers[$i]);
		}
		
		// LeadSkill
		$expectedHP = round($monStats[0]*0.33) + $hpRune + $monStats[0];
		$leadSkill = $monster->get_leadSkill();
		$monster->add_leader_skill_bonus($leadSkill);
		$newHp = $monster->get_runes_stat(STATS::HP);
		if ( $newHp != $expectedHP )	return array(false,"Erreur Leader Skill, HP = ".$newHp." au lieu de ".$expectedHP);
		
		return array(true,"OK");
	}
	
	
	//Créé Veromos avec un buff et un debuff atk, lui fait lancer ses 2 sorts et vérifie le cooldown des sorts et des buffs/debuffs
	function tf_monstre_cooldown(){
		
		$runeCode = "110110110000000100000100101110111100101100101110111100101000001010011000001000001010011000001100101110111100101000001010011000001";
		$monster = MonsterLib::load_monster("Veromos",$runeCode);
		
		$nbTours = 3;
		$debuffAtk = array("perCent"=>50,"type"=>DEBUFF::LOWATK,"turns"=>$nbTours);
		$buffAtk = array("perCent"=>50,"type"=>BUFF::RAISEATK,"turns"=>$nbTours);
		
		$monster->add_debuff($debuffAtk);
		$monster->add_buff($buffAtk);
		
		// Launch super crush
		$monster->choose_skill(1);
		// Check skill countdown
		$cdMegaSmash = $monster->get_cooldowns()[0];
		$cdSuperCrush = $monster->get_cooldowns()[1];
		if ( $cdMegaSmash != 0 ) return array(false,"Le sort mega smash devrait etre en cooldown pour 0 tour, pas ".$cdMegaSmash);
		if ( $cdSuperCrush != 3 ) return array(false,"Le sort super crush devrait etre en cooldown pour 3 tours, pas ".$cdSuperCrush);
		
		// Cooldown
		// Skill:4=>3 ; BUFF:3=>2 ; DEBUFF:3=>2
		$monster->cooldown();
		// Check skill countdown
		$cdSuperCrush = $monster->get_cooldowns()[1];
		if ( $cdSuperCrush != 2 ) return array(false,"Le sort super crush devrait etre en cooldown pour 2 tours, pas ".$cdSuperCrush);
		
		// Check buff cooldown
		$cdBuff = $monster->get_buffs()[0]["turns"];
		if ( $cdBuff != 2 ) return array(false,"Le buff atk devrait etre en cooldown pour 2 tours, pas ".$cdBuff);
		// Check debuff cooldown
		$cdDebuff = $monster->get_debuffs()[0]["turns"];
		if ( $cdDebuff != 2 ) return array(false,"Le debuff atk devrait etre en cooldown pour 2 tours, pas ".$cdDebuff);
		
		// Launch mega smash
		$monster->choose_skill(0);
		// Check skill countdown
		$cdMegaSmash = $monster->get_cooldowns()[0];
		if ( $cdMegaSmash != 1 ) return array(false,"Le sort mega smash devrait etre en cooldown pour 1 tour, pas ".$cdMegaSmash);
		
		// Cooldown
		$monster->cooldown();
		// Check skill countdown
		$cdMegaSmash = $monster->get_cooldowns()[0];
		$cdSuperCrush = $monster->get_cooldowns()[1];
		if ( $cdMegaSmash != 0 ) return array(false,"Le sort mega smash devrait etre en cooldown pour 0 tour, pas ".$cdMegaSmash);
		if ( $cdSuperCrush != 1 ) return array(false,"Le sort super crush devrait etre en cooldown pour 1 tours, pas ".$cdSuperCrush);
		
		// Check buff cooldown
		$cdBuff = $monster->get_buffs()[0]["turns"];
		if ( $cdBuff != 1 ) return array(false,"Le buff atk devrait etre en cooldown pour 1 tours, pas ".$cdBuff);
		// Check debuff cooldown
		$cdDebuff = $monster->get_debuffs()[0]["turns"];
		if ( $cdDebuff != 1 ) return array(false,"Le debuff atk devrait etre en cooldown pour 1 tours, pas ".$cdDebuff);
		
		return array(true,"OK");
	}
	
	//Créé Veromos avec buff atk, def, spd et cr et vérifie le bonus sur les stats en combat, puis lance 4 strip et vérifie le retour aux stats de départ
	function tf_monstre_stats_buff_strip(){
		$runeCode = "110110110000000100000100101110111100101100101110111100101000001010011000001000001010011000001100101110111100101000001010011000001";
		$monster = MonsterLib::load_monster("Veromos",$runeCode);
		
		$buffAtk = array("perCent"=>50,"type"=>BUFF::RAISEATK,"turns"=>2);
		$buffSpd = array("perCent"=>25,"type"=>BUFF::RAISESPD,"turns"=>3);
		$buffDef = array("perCent"=>75,"type"=>BUFF::RAISEDEF,"turns"=>1);
		$buffCr = array("perCent"=>75,"type"=>BUFF::RAISECR,"turns"=>1);
		$monster->add_buff($buffAtk);
		$monster->add_buff($buffDef);
		$monster->add_buff($buffSpd);
		$monster->add_buff($buffCr);
		
		
		// Calculate expected modifiers
		$monStats = $monster->get_runes_stats();
		$expectedAtkMod = round ( $monStats[STATS::ATK] * 0.5 );
		$expectedDefMod = round ( $monStats[STATS::DEF] * 0.7 );
		$expectedSpdMod = round ( $monStats[STATS::SPD] * 0.3 );
		$expectedCRMod = 30;
		
		// Check buff modifiers
		$bm = $monster->get_buff_mod();
		if ( $bm[STATS::ATK] != $expectedAtkMod ) return array(false,"Erreur buff modificateur ATK : ".$bm[STATS::ATK]." au lieu de ".$expectedAtkMod);
		if ( $bm[STATS::DEF] != $expectedDefMod ) return array(false,"Erreur buff modificateur DEF : ".$bm[STATS::DEF]." au lieu de ".$expectedDefMod);
		if ( $bm[STATS::SPD] != $expectedSpdMod ) return array(false,"Erreur buff modificateur SPD : ".$bm[STATS::SPD]." au lieu de ".$expectedSpdMod);
		if ( $bm[STATS::CR] != $expectedCRMod ) return array(false,"Erreur buff modificateur CR : ".$bm[STATS::CR]." au lieu de ".$expectedCRMod);
		
		
		// Calculate expected fightStats
		$expectedAtk = $monStats[STATS::ATK] + $expectedAtkMod;
		$expectedDef = $monStats[STATS::DEF] + $expectedDefMod;
		$expectedSpd = $monStats[STATS::SPD] + $expectedSpdMod;
		$expectedCR = $monStats[STATS::CR] + $expectedCRMod;
		
		// Check fightStats
		$fs = $monster->get_fight_stats();
		if ( $fs[STATS::ATK] != $expectedAtk ) return array(false,"Erreur buff stat ATK : ".$fs[STATS::ATK]." au lieu de ".$expectedAtk);
		if ( $fs[STATS::DEF] != $expectedDef ) return array(false,"Erreur buff stat DEF : ".$fs[STATS::DEF]." au lieu de ".$expectedDef);
		if ( $fs[STATS::SPD] != $expectedSpd ) return array(false,"Erreur buff stat SPD : ".$fs[STATS::SPD]." au lieu de ".$expectedSpd);
		if ( $fs[STATS::CR] != $expectedCR ) return array(false,"Erreur buff stat CR : ".$fs[STATS::CR]." au lieu de ".$expectedCR);
		
		
		// Strip ATK
		$monster->remove_buff();
		$bm = $monster->get_buff_mod()[STATS::ATK];
		$fs = $monster->get_fight_stats()[STATS::ATK];
		if ( $bm != 0 ) return array(false,"Erreur strip modificateur ATK : ".$bm." au lieu de 0");
		if ( $fs != $monStats[STATS::ATK] ) return array(false,"Erreur strip stat ATK : ".$fs." au lieu de ".$monStats[STATS::ATK]);
		
		// Strip DEF
		$monster->remove_buff();
		$bm = $monster->get_buff_mod()[STATS::DEF];
		$fs = $monster->get_fight_stats()[STATS::DEF];
		if ( $bm != 0 ) return array(false,"Erreur strip modificateur DEF : ".$bm." au lieu de 0");
		if ( $fs != $monStats[STATS::DEF] ) return array(false,"Erreur strip stat DEF : ".$fs." au lieu de ".$monStats[STATS::DEF]);
		
		// Strip SPD
		$monster->remove_buff();
		$bm = $monster->get_buff_mod()[STATS::SPD];
		$fs = $monster->get_fight_stats()[STATS::SPD];
		if ( $bm != 0 ) return array(false,"Erreur strip modificateur SPD : ".$bm." au lieu de 0");
		if ( $fs != $monStats[STATS::SPD] ) return array(false,"Erreur strip stat SPD : ".$fs." au lieu de ".$monStats[STATS::SPD]);
		
		// Strip CR
		$monster->remove_buff();
		$bm = $monster->get_buff_mod()[STATS::CR];
		$fs = $monster->get_fight_stats()[STATS::CR];
		if ( $bm != 0 ) return array(false,"Erreur strip modificateur CR : ".$bm." au lieu de 0");
		if ( $fs != $monStats[STATS::CR] ) return array(false,"Erreur strip stat CR : ".$fs." au lieu de ".$monStats[STATS::CR]);
		
		return array(true,"OK");
	}
	
	//Créé un monstre avec debuff atk, def et spd et vérifie le malus sur les stats en combat, puis lance 4 cleanse et vérifie le retour aux stats de départ
	function tf_monstre_stats_debuff_cleanse(){
		$runeCode = "110110110000000100000100101110111100101100101110111100101000001010011000001000001010011000001100101110111100101000001010011000001";
		$monster = MonsterLib::load_monster("Veromos",$runeCode);
		
		$debuffAtk = array("perCent"=>50,"type"=>DEBUFF::LOWATK,"turns"=>2);
		$debuffDef = array("perCent"=>25,"type"=>DEBUFF::LOWDEF,"turns"=>3);
		$debuffSpd = array("perCent"=>75,"type"=>DEBUFF::LOWSPD,"turns"=>1);
		$monster->add_debuff($debuffAtk);
		$monster->add_debuff($debuffDef);
		$monster->add_debuff($debuffSpd);
		
		
		// Calculate expected modifiers
		$monStats = $monster->get_runes_stats();
		$expectedAtkMod = round ( $monStats[STATS::ATK] * -0.5 );
		$expectedDefMod = round ( $monStats[STATS::DEF] * -0.7 );
		$expectedSpdMod = round ( $monStats[STATS::SPD] * -0.3 );
		
		// Check buff modifiers
		$bm = $monster->get_buff_mod();
		if ( $bm[STATS::ATK] != $expectedAtkMod ) return array(false,"Erreur debuff modificateur ATK : ".$bm[STATS::ATK]." au lieu de ".$expectedAtkMod);
		if ( $bm[STATS::DEF] != $expectedDefMod ) return array(false,"Erreur debuff modificateur DEF : ".$bm[STATS::DEF]." au lieu de ".$expectedDefMod);
		if ( $bm[STATS::SPD] != $expectedSpdMod ) return array(false,"Erreur debuff modificateur SPD : ".$bm[STATS::SPD]." au lieu de ".$expectedSpdMod);
		
		
		// Calculate expected fightStats
		$expectedAtk = $monStats[STATS::ATK] + $expectedAtkMod;
		$expectedDef = $monStats[STATS::DEF] + $expectedDefMod;
		$expectedSpd = $monStats[STATS::SPD] + $expectedSpdMod;
		
		// Check fightStats
		$fs = $monster->get_fight_stats();
		if ( $fs[STATS::ATK] != $expectedAtk ) return array(false,"Erreur debuff stat ATK : ".$fs[STATS::ATK]." au lieu de ".$expectedAtk);
		if ( $fs[STATS::DEF] != $expectedDef ) return array(false,"Erreur debuff stat DEF : ".$fs[STATS::DEF]." au lieu de ".$expectedDef);
		if ( $fs[STATS::SPD] != $expectedSpd ) return array(false,"Erreur debuff stat SPD : ".$fs[STATS::SPD]." au lieu de ".$expectedSpd);
		
		
		// Cleanse ATK
		$monster->remove_debuff();
		$bm = $monster->get_buff_mod()[STATS::ATK];
		$fs = $monster->get_fight_stats()[STATS::ATK];
		if ( $bm != 0 ) return array(false,"Erreur modificateur ATK : ".$bm." au lieu de 0");
		if ( $fs != $monStats[STATS::ATK] ) return array(false,"Erreur stat ATK : ".$fs." au lieu de ".$monStats[STATS::ATK]);
		// Cleanse DEF
		$monster->remove_debuff();
		$bm = $monster->get_buff_mod()[STATS::DEF];
		$fs = $monster->get_fight_stats()[STATS::DEF];
		if ( $bm != 0 ) return array(false,"Erreur modificateur DEF : ".$bm." au lieu de 0");
		if ( $fs != $monStats[STATS::DEF] ) return array(false,"Erreur stat DEF : ".$fs." au lieu de ".$monStats[STATS::DEF]);
		// Cleanse SPD
		$monster->remove_debuff();
		$bm = $monster->get_buff_mod()[STATS::SPD];
		$fs = $monster->get_fight_stats()[STATS::SPD];
		if ( $bm != 0 ) return array(false,"Erreur modificateur SPD : ".$bm." au lieu de 0");
		if ( $fs != $monStats[STATS::SPD] ) return array(false,"Erreur stat SPD : ".$fs." au lieu de ".$monStats[STATS::SPD]);
		
		return array(true,"OK");
	}
	
	//Vérifie les stats en combat pour les buff/debuff sur la meme stat : buff atk, debuff atk, strip buff, cleanse debuff 
	function tf_monstre_stats_buff_debuff(){
		$runeCode = "110110110000000100000100101110111100101100101110111100101000001010011000001000001010011000001100101110111100101000001010011000001";
		$monster = MonsterLib::load_monster("Veromos",$runeCode);
		
		$debuffAtk = array("perCent"=>50,"type"=>DEBUFF::LOWATK,"turns"=>2);
		$buffAtk = array("perCent"=>50,"type"=>BUFF::RAISEATK,"turns"=>2);
		
		// Calculate expected results
		$monStats = $monster->get_runes_stats();
		$modStat = round($monStats[STATS::ATK]*0.5);
		$regularStat = $monStats[STATS::ATK];
		$buffedStat = round($monStats[STATS::ATK]*1.5);
		$debuffedStat = $modStat;
		
		$monster->add_buff($buffAtk);
		$bm = $monster->get_buff_mod();
		$fs = $monster->get_fight_stats()[STATS::ATK];
		if ( $bm[STATS::ATK] != $modStat ) return array(false,"Erreur modificateur ATK after buff : ".$bm[STATS::ATK]." au lieu de ".$modStat);
		if ( $fs != $buffedStat ) return array(false,"Erreur stat ATK after buff : ".$fs." au lieu de ".$buffedStat);
		
		$monster->add_debuff($debuffAtk);
		$bm = $monster->get_buff_mod();
		$fs = $monster->get_fight_stats()[STATS::ATK];
		if ( $bm[STATS::ATK] != 0 ) return array(false,"Erreur modificateur ATK after debuff : ".$bm[STATS::ATK]." au lieu de 0");
		if ( $fs != $regularStat ) return array(false,"Erreur stat ATK after debuff : ".$fs." au lieu de ".$regularStat);
		
		$monster->remove_buff();
		$bm = $monster->get_buff_mod()[STATS::ATK];
		$fs = $monster->get_fight_stats()[STATS::ATK];
		if ( $bm != -1*$modStat ) return array(false,"Erreur modificateur ATK after strip : ".$bm." au lieu de -".$modStat);
		if ( $fs != $debuffedStat ) return array(false,"Erreur stat ATK after strip : ".$fs." au lieu de ".$debuffedStat);
		
		$monster->remove_debuff();
		$bm = $monster->get_buff_mod()[STATS::ATK];
		$fs = $monster->get_fight_stats()[STATS::ATK];
		if ( $bm != 0 ) return array(false,"Erreur modificateur ATK after cleanse : ".$bm." au lieu de 0");
		if ( $fs != $regularStat ) return array(false,"Erreur stat ATK after cleanse : ".$fs." au lieu de ".$regularStat);
		
		return array(true,"OK");
	}
	
	//Créé un monstre, lui inflige des dégats et vérifie la perte de pv puis ajoute un buff heal et vérifie le gain de pv dans les stats de combat
	function tf_monstre_takeHit_heal(){
		$runeCode = "110110110000000100000100101110111100101110111000001010011000001010011100101110111000001010011";
		$monster = MonsterLib::load_monster("Veromos",$runeCode);
		
		// Take hit
		$dmg = 3000;
		$pvAvant = $monster->get_fight_stat(STATS::HP);
		$monster->take_hit($dmg);
		$pvApres = $monster->get_fight_stat(STATS::HP);
		if ( $pvApres != $pvAvant - $dmg ) return array(false,"take_hit : Les PV devraient etre $pvAvant - $dmg = ".($pvAvant-$dmg).", pas $pvApres");
		
		// Heal
		$buffHeal = array("perCent"=>50,"type"=>BUFF::HEAL,"turns"=>2);
		$monster->add_buff($buffHeal);
		$pvMax = $monster->get_fight_stat(STATS::MAXHP);
		$pvAvant = $monster->get_fight_stat(STATS::HP);
		$healEffect = round($pvMax * 0.15);
		$monster->apply_heal_buff();
		$pvApres = $monster->get_fight_stat(STATS::HP);
		if ( $pvApres != $pvAvant + $healEffect ) return array(false,"heal 1 : Les PV devraient etre $pvAvant + $healEffect = ".($pvAvant+$healEffect).", pas $pvApres");
		// Re-heal
		$monster->apply_heal_buff();
		$pvApres = $monster->get_fight_stat(STATS::HP);
		if ( $pvApres > $pvMax ) return array(false,"heal 2 : Les PV devraient plafonner à ".($pvMax).", pas $pvApres");
		
		// TODO : appliquer DEBUFF::NOHEAL et verifier l'absence de soin
		
		return array(true,"OK");
	}
	
	//Créé un monstre avec 3 debuff constDmg et vérifie leur cumul et la perte de hp dans les stats de combat
	function tf_monstre_constant_dmg(){
		$runeCode = "110110110000000100000100101110111100101110111000001010011000001010011100101110111000001010011";
		$monster = MonsterLib::load_monster("Veromos",$runeCode);
		
		// Verifier que la fonction n'applique aucun dégats quand le debuff n'est pas encore posé
		$nb = $monster->apply_constant_damages();
		if ( $nb != 0 ) return array(false,"Aurait du appliquer les dégats constants 0 fois, pas $nb");
		
		$debuffDmg = array("perCent"=>50,"type"=>DEBUFF::CONSTDMG,"turns"=>2);
		$monster->add_debuff($debuffDmg);
		
		$pvAvant = $monster->get_fight_stat(STATS::HP);
		$dmg = round($monster->get_fight_stat(STATS::MAXHP) * 0.05);
		$nb = $monster->apply_constant_damages();
		if ( $nb != 1 ) return array(false,"Aurait du appliquer les dégats constants 1 fois, pas $nb");
		$pvApres = $monster->get_fight_stat(STATS::HP);
		if ( $pvApres != $pvAvant - $dmg ) return array(false,"Les PV devraient etre $pvAvant - $dmg = ".($pvAvant-$dmg).", pas $pvApres");
		
		// verifier cumul des degats constants
		$monster->add_debuff($debuffDmg);
		$monster->add_debuff($debuffDmg);
		$pvAvant = $pvApres;
		$nb = $monster->apply_constant_damages();
		if ( $nb != 3 ) return array(false,"Aurait du appliquer les dégats constants 3 fois, pas $nb");
		$pvApres = $monster->get_fight_stat(STATS::HP);
		if ( $pvApres != $pvAvant-(3*$dmg) ) return array(false,"Les PV devraient etre $pvAvant - (3*$dmg) = ".($pvAvant-($dmg*3)).", pas $pvApres");
		
		return array(true,"OK");
	}
	
	
	
	
	//////////////////////////////////////////////
	////////////	 SKILLS		//////////////////
	//////////////////////////////////////////////
	
	// Verifie les degats produits par un monstre donné avec une formule donnée
	function tf_skill_damage(){
		
		// Definition d'un monstre précis
		$main246 = array(STATS::DEF,STATS::DEF,STATS::DEF);
		$families = array(RUNEFAMILY::Despair,RUNEFAMILY::Will);
		$sub1 = array(STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP);
		$sub2 = array(STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP);
		$sub3 = array(STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP);
		$sub4 = array(STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP);
		$sub5 = array(STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP);
		$sub6 = array(STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP);
		$subs = array($sub1,$sub2,$sub3,$sub4,$sub5,$sub6);
		$runeCode = Runage::encode_runage($main246,$families,$subs);
		$vero = MonsterLib::load_monster("Veromos",$runeCode);
		
		// Test sort 1
		$skill_megaSmash = $vero->get_skill(0);
		$action_dmgOne = $skill_megaSmash->get_actions()[0];
		$effect_damage = $action_dmgOne->get_effects()[0];
		$dmg = $effect_damage->get_damage($vero->get_fight_stats());
		if ( $dmg != 3844 ) return array(false,"Le sort Mega Smash de Veromos doit faire 3844 de dégats, pas $dmg");
		
		// Test sort 2
		$skill_superCrush = $vero->get_skill(1);
		$action_dmgAll = $skill_superCrush->get_actions()[0];
		$effect_damage = $action_dmgAll->get_effects()[0];
		$dmg = $effect_damage->get_damage($vero->get_fight_stats());
		if ( $dmg != 5593 ) return array(false,"Le sort Super Crush de Veromos doit faire 5593 de dégats, pas $dmg");
		
		return array(true,"OK");
	}
	
	
	
	
	//////////////////////////////////////////////
	////////////	COMBAT		//////////////////
	//////////////////////////////////////////////
	
	//Teste les avantages elementaires
	function tu_combat_avantage_element(){
		
		if ( $v = Combat::has_advantage(ELMT::water,ELMT::water) != 0 ) return array(false,"Eau vs Eau doit donner 0, pas $v");
		if ( $v = Combat::has_advantage(ELMT::water,ELMT::fire) != 1 ) return array(false,"Eau vs Feu doit donner 1, pas $v");
		if ( $v = Combat::has_advantage(ELMT::water,ELMT::wind) != -1 ) return array(false,"Eau vs Vent doit donner -1, pas $v");
		if ( $v = Combat::has_advantage(ELMT::water,ELMT::dark) != 0 ) return array(false,"Eau vs Ombre doit donner 0, pas $v");
		if ( $v = Combat::has_advantage(ELMT::water,ELMT::light) != 0 ) return array(false,"Eau vs Lumiere doit donner 0, pas $v");
		
		if ( $v = Combat::has_advantage(ELMT::fire,ELMT::water) != -1 ) return array(false,"Feu vs Eau doit donner -1, pas $v");
		if ( $v = Combat::has_advantage(ELMT::fire,ELMT::fire) != 0 ) return array(false,"Feu vs Feu doit donner 0, pas $v");
		if ( $v = Combat::has_advantage(ELMT::fire,ELMT::wind) != 1 ) return array(false,"Feu vs Vent doit donner 1, pas $v");
		if ( $v = Combat::has_advantage(ELMT::fire,ELMT::dark) != 0 ) return array(false,"Feu vs Ombre doit donner 0, pas $v");
		if ( $v = Combat::has_advantage(ELMT::fire,ELMT::light) != 0 ) return array(false,"Feu vs Lumiere doit donner 0, pas $v");
		
		if ( $v = Combat::has_advantage(ELMT::wind,ELMT::water) != 1 ) return array(false,"Vent vs Eau doit donner 1, pas $v");
		if ( $v = Combat::has_advantage(ELMT::wind,ELMT::fire) != -1 ) return array(false,"Vent vs Feu doit donner -1, pas $v");
		if ( $v = Combat::has_advantage(ELMT::wind,ELMT::wind) != 0 ) return array(false,"Vent vs Vent doit donner 0, pas $v");
		if ( $v = Combat::has_advantage(ELMT::wind,ELMT::dark) != 0 ) return array(false,"Vent vs Ombre doit donner 0, pas $v");
		if ( $v = Combat::has_advantage(ELMT::wind,ELMT::light) != 0 ) return array(false,"Vent vs Lumiere doit donner 0, pas $v");
		
		if ( $v = Combat::has_advantage(ELMT::dark,ELMT::water) != 0 ) return array(false,"Ombre vs Eau doit donner 0, pas $v");
		if ( $v = Combat::has_advantage(ELMT::dark,ELMT::fire) != 0 ) return array(false,"Ombre vs Feu doit donner 0, pas $v");
		if ( $v = Combat::has_advantage(ELMT::dark,ELMT::wind) != 0 ) return array(false,"Ombre vs Vent doit donner 0, pas $v");
		if ( $v = Combat::has_advantage(ELMT::dark,ELMT::dark) != 0 ) return array(false,"Ombre vs Ombre doit donner 0, pas $v");
		if ( $v = Combat::has_advantage(ELMT::dark,ELMT::light) != 1 ) return array(false,"Ombre vs Lumiere doit donner 1, pas $v");
		
		if ( $v = Combat::has_advantage(ELMT::light,ELMT::water) != 0 ) return array(false,"Lumiere vs Eau doit donner 0, pas $v");
		if ( $v = Combat::has_advantage(ELMT::light,ELMT::fire) != 0 ) return array(false,"Lumiere vs Feu doit donner 0, pas $v");
		if ( $v = Combat::has_advantage(ELMT::light,ELMT::wind) != 0 ) return array(false,"Lumiere vs Vent doit donner 0, pas $v");
		if ( $v = Combat::has_advantage(ELMT::light,ELMT::dark) != 1 ) return array(false,"Lumiere vs Ombre doit donner 1, pas $v");
		if ( $v = Combat::has_advantage(ELMT::light,ELMT::light) != 0 ) return array(false,"Lumiere vs Lumiere doit donner 0, pas $v");
		
		return array(true,"OK");
	}
	
	//Verifie le mécanisme PrecisionAttaquant / ResistanceDefenseur / TauxApplicationCompetence
	function tu_combat_resistance(){
		
		// Test 1 : Res=100, Pre=0 => apply doit etre à 0
		$nbApply = 0;
		$attackPre = 0;
		$defRes = 100;
		$effectRate = 100;
		for ($i=0;$i<100;$i++){			
			if ( Combat::effect_applies($attackPre,$defRes,$effectRate) )	$nbApply++;
		}
		if ( $nbApply > 0 )	return array(false,"Avec une resistance de 100 et une precision de 0, 0% des effets devraient etre appliqués. $nbApply sont passés");
		
		
		// Test 2 Res=100, Pre=85 => apply doit etre à ~85
		$nbApply = 0;
		$attackPre = 85;
		$defRes = 100;
		$effectRate = 100;
		for ($i=0;$i<100;$i++){			
			if ( Combat::effect_applies($attackPre,$defRes,$effectRate) )	$nbApply++;
		}
		if ( $nbApply<80 && $nbApply>90 )	return array(false,"Avec une resistance de 100 et une precision de 85, environ 85% des effets devraient etre appliqués. $nbApply% sont passés");
		
		
		// Test 3 : Res=15, Pre=/ => apply doit etre à ~85
		$nbApply = 0;
		$attackPre = 50;
		$defRes = 15;
		$effectRate = 100;
		for ($i=0;$i<100;$i++){			
			if ( Combat::effect_applies($attackPre,$defRes,$effectRate) )	$nbApply++;
		}
		if ( $nbApply<80 && $nbApply>90 )	return array(false,"Avec une resistance de 15, quelle que soit la precision, environ 85% des effets devraient etre appliqués. $nbApply% sont passés");
		
		
		// Test 4 : effectRate=0 => apply doit etre à 0
		$nbApply = 0;
		$attackPre = 50;
		$defRes = 15;
		$effectRate = 0;
		for ($i=0;$i<100;$i++){			
			if ( Combat::effect_applies($attackPre,$defRes,$effectRate) )	$nbApply++;
		}
		if ( $nbApply>0 )	return array(false,"Avec un taux de 0, quelle que soit la resistance et la precision, aucun effet ne devrait etre appliqué. $nbApply% sont passés");
		
		
		// Test 5 : random
		$nbApply = 0;
		$attackPre = mt_rand(0,100);
		$defRes = mt_rand(15,100);
		$effectRate = mt_rand(0,100);
		$chRes = $defRes - $attackPre;
		if ( $chRes < 15 ) $chRes = 15;
		$chApply = 100 - $chRes;
		$chHit = $chApply * $effectRate / 100;
		for ($i=0;$i<100;$i++){			
			if ( Combat::effect_applies($attackPre,$defRes,$effectRate) )	$nbApply++;
		}
		if ( $nbApply<($chHit-7) && $nbApply>($chHit+7) )	return array(false,"Avec une resistance de $defRes, une precision de $attackPre et un taux de $effectRate, environ $chHit% des effets devraient etre appliqués. $nbApply% sont passés");
		
		return array(true,"OK");
	}
	
	
	//Aplique le leader skill en combat
	function tf_combat_leadSkill(){
		
		$runeCode = "";
		for ( $i=0; $i<129; $i++ )	$runeCode .= mt_rand(0,1);
		$vero = MonsterLib::load_monster("Veromos",$runeCode);
		$barreta = MonsterLib::load_monster("Baretta",$runeCode);
		$bella1 = MonsterLib::load_monster("Belladeon",$runeCode);
		$lushen = MonsterLib::load_monster("Lushen",$runeCode);
		$bernard = MonsterLib::load_monster("Bernard",$runeCode);
		$bella2 = MonsterLib::load_monster("Belladeon",$runeCode);
		$team1 = array($vero,$barreta,$bella1);
		$team2 = array($lushen,$bernard,$bella2);
		$combat = new Combat($team1,$team2);
		
		$teams = $combat->get_teams();
		$veroHP = $teams[1][0]->get_fight_stat(STATS::HP);
		$barettaHP = $teams[1][1]->get_fight_stat(STATS::HP);
		$bellaHP = $teams[1][2]->get_fight_stat(STATS::HP);
		$lushenAtk = $teams[2][0]->get_fight_stat(STATS::ATK);
		$bernardAtk = $teams[2][1]->get_fight_stat(STATS::ATK);
		$bella2Atk = $teams[2][2]->get_fight_stat(STATS::ATK);
		/////////////////////////////////////////
		$combat->apply_leader_skill();
		/////////////////////////////////////////
		$veroHPapres = $teams[1][0]->get_fight_stat(STATS::HP);
		$barettaHPapres = $teams[1][1]->get_fight_stat(STATS::HP);
		$bellaHPapres = $teams[1][2]->get_fight_stat(STATS::HP);
		$lushenAtkapres = $teams[2][0]->get_fight_stat(STATS::ATK);
		$bernardAtkapres = $teams[2][1]->get_fight_stat(STATS::ATK);
		$bella2Atkapres = $teams[2][2]->get_fight_stat(STATS::ATK);
		if ( $veroHPapres <= $veroHP )			return array(false,"Leader skill de Veromos pas appliqué à lui-même (avant:$veroHP => apres:$veroHPapres)");
		if ( $barettaHPapres <= $barettaHP )	return array(false,"Leader skill de Veromos pas appliqué à Baretta (avant:$barettaHP => apres:$barettaHPapres)");
		if ( $bellaHPapres <= $bellaHP )		return array(false,"Leader skill de Veromos pas appliqué à Belladeon (avant:$bellaHP => apres:$bellaHPapres)");
		if ( $lushenAtkapres != $lushenAtk )	return array(false,"Leader skill de Lushen appliqué hors dongeon (avant:$lushenAtk => apres:$lushenAtkapres)");
		if ( $bernardAtkapres != $bernardAtk )	return array(false,"Leader skill de Lushen appliqué hors dongeon (avant:$bernardAtk => apres:$bernardAtkapres)");
		if ( $bella2Atkapres != $bella2Atk )	return array(false,"Leader skill de Lushen appliqué hors dongeon (avant:$bella2Atk => apres:$bella2Atkapres)");
		
		$team1 = array($bernard,$lushen,$bella1);
		$team2 = array($barreta,$vero,$bella2);
		$combat = new Combat($team1,$team2);
		$teams = $combat->get_teams();
		$bernardAtk = $teams[1][0]->get_fight_stat(STATS::ATK);
		$lushenAtk = $teams[1][1]->get_fight_stat(STATS::ATK);
		$bellaAtk = $teams[1][2]->get_fight_stat(STATS::ATK);
		$barettaSpd = $teams[2][0]->get_fight_stat(STATS::SPD);
		$veroSpd = $teams[2][1]->get_fight_stat(STATS::SPD);
		$bella2Spd = $teams[2][2]->get_fight_stat(STATS::SPD);
		/////////////////////////////////////////
		$combat->apply_leader_skill();
		/////////////////////////////////////////
		$bernardAtkapres= $teams[1][0]->get_fight_stat(STATS::ATK);
		$lushenAtkapres = $teams[1][1]->get_fight_stat(STATS::ATK);
		$bellaAtkapres = $teams[1][2]->get_fight_stat(STATS::ATK);
		$barettaSpdapres = $teams[2][0]->get_fight_stat(STATS::SPD);
		$veroSpdapres = $teams[2][1]->get_fight_stat(STATS::SPD);
		$bella2Spdapres = $teams[2][2]->get_fight_stat(STATS::SPD);
		if ( $bernardAtkapres <= $bernardAtk )		return array(false,"Leader skill de Bernard (vent:atk+30%) pas appliqué à lui-même (avant:$bernardAtk => apres:$bernardAtkapres)");
		if ( $lushenAtkapres <= $lushenAtk )		return array(false,"Leader skill de Bernard (vent:atk+30%) pas appliqué à Lushen (avant:$lushenAtk => apres:$lushenAtkapres)");
		if ( $bellaAtkapres != $bellaAtk )			return array(false,"Leader skill de Bernard (vent:atk+30%) appliqué à Belladeon (ATK avant:$bellaAtk => ATK apres:$bellaAtkapres)");
		if ( $barettaSpdapres <= $barettaSpd )		return array(false,"Leader skill de Baretta (spd+19%) pas appliqué à lui-même (spd avant:$barettaSpd => spd apres:$barettaSpdapres)");
		if ( $veroSpdapres <= $veroSpd )			return array(false,"Leader skill de Baretta (spd+19%) pas appliqué à Veromos (spd avant:$veroSpd => spd apres:$veroSpdapres)");
		if ( $bella2Spdapres <= $bella2Spd )		return array(false,"Leader skill de Baretta (spd+19%) pas appliqué à lui-même (spd avant:$bella2Spd => spd apres:$bella2Spdapres)");
		
		return array(true,"OK");
	}
	
	//Verifie que la barre d attaque est bien remplie pour l un des monstres et que ce monstre est choisi pour jouer
	function tf_combat_run(){
		
		$runeCode = "111000110000000000000";
		for ( $i=0; $i<108; $i++ )	$runeCode .= mt_rand(0,1);
		$bernard = MonsterLib::load_monster("Bernard",$runeCode);
		
		$runeCode = "";
		for ( $i=0; $i<129; $i++ )	$runeCode .= mt_rand(0,1);
		$lushen = MonsterLib::load_monster("Lushen",$runeCode);
		$vero = MonsterLib::load_monster("Veromos",$runeCode);
		$barreta = MonsterLib::load_monster("Baretta",$runeCode);
		$team1 = array($barreta,$bernard,$vero);
		$team2 = array($lushen,$vero,$barreta);
		$combat = new Combat($team1,$team2);
		
		// Verifier qu'au moins une ATB est > 100, garder l'ID du monstre le + haut
		$combat->tick();
		$atbTest = false;
		$idFastestMonster;
		$greaterATB = 100;
		foreach ( $combat->get_speedCounter() as $id => $atb ){
			if ( $atb > 100 ){
				$atbTest = true;
				if ( $atb > $greaterATB ){
					$idFastestMonster = $id;
					$greaterATB = $atb;
				}
			}
		}
		if ( !$atbTest )	return array(false,"Aucune vitesse supérieure à 100");
		
		
		// Verifier concordance avec le monstre choisi pour jouer
		$combat->select_playing_monster();
		$idPlayingMonster = $combat->get_playingMonster_speedcounterID();
		if ( $idPlayingMonster != $idFastestMonster )	return array(false,"Le monstre choisi ($idPlayingMonster) n est pas le plus rapide ($idFastestMonster)");
		
		return array(true,"OK");
	}
	
	//Verifie le choix des cibles pour chaque ciblage possible
	function tf_combat_target(){
		
		// Definition runage
		$runeCode = "";
		for ( $i=0; $i<129; $i++ )	$runeCode .= mt_rand(0,1);
		
		// Monstres
		$vero = MonsterLib::load_monster("Veromos",$runeCode);
		$beber = MonsterLib::load_monster("Bernard",$runeCode);
		$barett = MonsterLib::load_monster("Baretta",$runeCode);
		
		// Equipes & combat
		$team1 = array($vero,$beber,$barett);
		$team2 = array($vero,$beber,$barett);
		$combat = new Combat($team1,$team2,false);
		
		// Contexte
		$combat->set_turn_context(1,0);		// tour de veromos
		$skill = $vero->get_skill(0);	// smash : target 1 ennemy
		$action = $skill->get_actions()[0];
		$combat->choose_targets($action);
		$t = $combat->get_targets();
		$targetSide = $t[0];
		$targetMonsters = $t[1];
		if ( $targetSide != 2)				return array(false,"MegaSmash de Veromos doit cibler l equipe ennemie");
		if ( count($targetMonsters) != 1 )	return array(false,"MegaSmash de Veromos ne doit cibler qu un seul monstre");
		
		$skill = $vero->get_skill(1);	// crush : target all ennemies
		$action = $skill->get_actions()[0];
		$combat->choose_targets($action);
		$t = $combat->get_targets();
		$targetSide = $t[0];
		$targetMonsters = $t[1];
		if ( $targetSide != 2)				return array(false,"SuperCrush de Veromos doit cibler l equipe ennemie");
		if ( count($targetMonsters) != 3 )	return array(false,"SuperCrush de Veromos doit cibler tous les monstres ennemis");
		
		$combat->set_turn_context(1,1);		// tour de Bernard
		$skill = $beber->get_skill(2);	// TailWind : target all allies
		$action = $skill->get_actions()[0];
		$combat->choose_targets($action);
		$t = $combat->get_targets();
		$targetSide = $t[0];
		$targetMonsters = $t[1];
		if ( $targetSide != 1)				return array(false,"TailWind de Bernard doit cibler l equipe alliée");
		if ( count($targetMonsters) != 3 )	return array(false,"TailWind de Bernard doit cibler tous les monstres alliés");
		
		$combat->set_turn_context(1,2);		// tour de Baretta
		$skill = $barett->get_skill(0);	// Spirit : target self
		$action = $skill->get_actions()[1];
		$combat->choose_targets($action);
		$t = $combat->get_targets();
		$targetSide = $t[0];
		$targetMonsters = $t[1];
		if ( $targetSide != 1)				return array(false,"SpiritThrow de Baretta doit cibler l equipe alliée");
		if ( count($targetMonsters) != 1 )	return array(false,"SpiritThrow de Baretta ne doit cibler qu un seul monstre");
		if ( $targetMonsters[0] != 2 )		return array(false,"SpiritThrow de Baretta doit cibler Baretta lui-même");
		
		return array(true,"OK");
	}
	
	//Verifie les degats infligés à une cible donnée avec un skill de dégat donné
	function tf_combat_damage(){
		
		// Definition runage
		$main246 = array(STATS::DEF,STATS::DEF,STATS::DEF);
		$families = array(RUNEFAMILY::Despair,RUNEFAMILY::Will);
		$sub1 = array(STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP);
		$sub2 = array(STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP);
		$sub3 = array(STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP);
		$sub4 = array(STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP);
		$sub5 = array(STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP);
		$sub6 = array(STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP);
		$subs = array($sub1,$sub2,$sub3,$sub4,$sub5,$sub6);
		$runeCode = Runage::encode_runage($main246,$families,$subs);
		
		// TEST DES COUPS CRITIQUES (SANS AVANTAGE ELEMENTAIRE)
		///////////////////////////////////////////////////////
		// Monstre avec 0% critique
		$veroCR0 = MonsterLib::load_monster("Veromos",$runeCode);
		$veroCR0->set_fightStat(STATS::CR,0);
		// Monstre avec 100% critique
		$veroCR100 = MonsterLib::load_monster("Veromos",$runeCode);
		$veroCR100->set_fightStat(STATS::CR,100);
		
		// Definition equipes & combat
		$team1 = array($veroCR0,$veroCR100);
		$team2 = array($veroCR0);
		$combat = new Combat($team1,$team2,false);
		
		// Test : attaque sans critique
		$combat->set_turn_context(1,0);
		$skill = $veroCR0->get_skill(0);
		$hpavant = $combat->get_teams()[2][0]->get_fight_stats()[STATS::HP];
		$combat->apply_skill_actions($skill);
		$hpapres = $combat->get_teams()[2][0]->get_fight_stats()[STATS::HP];
		$dmg = $hpavant - $hpapres;
		if ( $dmg != 469 )	return array(false,"Le coup sans critique aurait du infliger 469 dégats , pas $dmg");
		
		// Test : attaque avec critique
		$combat->set_turn_context(1,1);
		$skill = $veroCR100->get_skill(0);
		$hpavant = $combat->get_teams()[2][0]->get_fight_stats()[STATS::HP];
		$combat->apply_skill_actions($skill);
		$hpapres = $combat->get_teams()[2][0]->get_fight_stats()[STATS::HP];
		$dmg = $hpavant - $hpapres;
		if ( $dmg != 703 )	return array(false,"Le coup avec critique aurait du infliger 703 dégats , pas $dmg");
		
		
		// TEST DES COUPS AVEC AVANTAGE ELEMENTAIRE
		///////////////////////////////////////////
		$fireDefMon = MonsterLib::load_monster("Baretta",$runeCode);
		$fireDefMon->set_fightStats(array(10000,500,500,100,0,0,0,0,10000));
		$fireDefMon2 = MonsterLib::load_monster("Baretta",$runeCode);
		$fireDefMon2->set_fightStats(array(10000,1000,650,100,0,0,0,0,10000));
		$windDefMon = MonsterLib::load_monster("Lushen",$runeCode);
		$windDefMon->set_fightStats(array(10000,1000,500,100,0,0,0,0,10000));
		$fireAtkMon = MonsterLib::load_monster("Baretta",$runeCode);
		$fireAtkMon->set_fightStats(array(10000,1000,500,100,0,0,0,0,10000));
		
		$team1 = array($fireAtkMon);
		$team2 = array($fireDefMon,$fireDefMon2,$windDefMon);
		$combat = new Combat($team1,$team2,false);
		
		$combat->set_turn_context(1,0);
		$skill = $fireAtkMon->get_skill(2);	// phoenix's fury : hit all ennemy
		$combat->apply_skill_actions($skill);
		
		$hpFireDefMon = $combat->get_teams()[2][0]->get_fight_stat(STATS::HP);
		$hpFireDefMon2 = $combat->get_teams()[2][1]->get_fight_stat(STATS::HP);
		$hpWindDefMon = $combat->get_teams()[2][2]->get_fight_stat(STATS::HP);
		
		// Test : dégats feu + important vs vent que vs feu
		$degatVSfeu = 10000 - $hpFireDefMon;
		$degatVSvent = 10000 - $hpWindDefMon;
		$expected = round($dégatVSfeu * 1.3);
		if ( $hpWindDefMon > $hpFireDefMon )	return array(false,"Face à une attaque de feu, le monstre de vent devrait perdre plus de HP que le monstre de feu (feu vs feu:$degatVSfeu ; feu vs vent:$degatVSvent");
		if ( $expected > $degatVSvent )			return array(false,"Face à une attaque de feu, le monstre de vent devrait prendre 30% plus de dégats que le monstre de feu (expected:$expected ; degats:$degatVSvent");
		
		// Test : dégats - important si defense + elevée
		if ( $hpFireDefMon > $hpFireDefMon2 )	return array(false,"Les dégats devraient être moins important contre un monstre avec une défense élevée");
		
		
		return array(true,"OK");
	}
	
	//Verifie les soins reçus en combat
	function tf_combat_heal(){
		
		// Definition runage
		$runeCode = "";
		for ( $i=0; $i<129; $i++ )	$runeCode .= mt_rand(0,1);
		
		// Monstres
		$vero1 = MonsterLib::load_monster("Veromos",$runeCode);
		$vero2 = MonsterLib::load_monster("Veromos",$runeCode);
		$bella = MonsterLib::load_monster("Belladeon",$runeCode);
		$lush = MonsterLib::load_monster("Lushen",$runeCode);
		
		// Equipes & combat
		$team1 = array($bella,$vero1,$vero2);
		$team2 = array($lush);
		$combat = new Combat($team1,$team2,false);
		
		// Lushen equipe 2 applique degats a tous les monstres equipe 1
		$combat->set_turn_context(2,0);
		$skill = $lush->get_skill(2);
		$combat->apply_skill_actions($skill);
		$hp0avant = $combat->get_teams()[1][0]->get_fight_stats()[STATS::HP];
		$hp1avant = $combat->get_teams()[1][1]->get_fight_stats()[STATS::HP];
		$hp2avant = $combat->get_teams()[1][2]->get_fight_stats()[STATS::HP];
		
		// Bella soigne equipe 1
		$combat->set_turn_context(1,0);
		$skill = $bella->get_skill(2);
		$combat->apply_skill_actions($skill);
		$hp0apres = $combat->get_teams()[1][0]->get_fight_stats()[STATS::HP];
		$hp1apres = $combat->get_teams()[1][1]->get_fight_stats()[STATS::HP];
		$hp2apres = $combat->get_teams()[1][2]->get_fight_stats()[STATS::HP];
		if ( $hp0apres <= $hp0avant )	return array(false,"Le monstre #0 aurait du récupérer ses HP (avant:$hp0avant apres:$hp0apres");
		if ( $hp1apres <= $hp1avant )	return array(false,"Le monstre #1 aurait du récupérer ses HP (avant:$hp1avant apres:$hp1apres");
		if ( $hp2apres <= $hp2avant )	return array(false,"Le monstre #2 aurait du récupérer ses HP (avant:$hp2avant apres:$hp2apres");
		
		return array(true,"OK");
	}
	
	//Verifie la modificaton de barre d attaque
	function tf_combat_changeATB(){
		
		// Runage
		$runeCode = "";
		for ( $i=0; $i<129; $i++ )	$runeCode .= mt_rand(0,1);
		
		// Monstres
		$bernard = MonsterLib::load_monster("Bernard",$runeCode);
		$bernard->set_fightStats(array(10000,400,600,100,0,0,100,0,10000));
		$baretta = MonsterLib::load_monster("Baretta",$runeCode);
		$baretta->set_fightStats(array(10000,400,600,100,0,0,100,0,10000));
		
		// Equipes & combat
		$team1 = array($bernard);
		$team2 = array($baretta);
		$combat = new Combat($team1,$team2,false);
		
		
		// Check Combat::apply_change_ATB()
		$combat->set_turn_context(1,0);
		$skill = $bernard->get_skill(2);
		$atbAvant = $combat->get_speedCounter()[0];
		$combat->apply_skill_actions($skill);
		$atbApres = $combat->get_speedCounter()[0];
		if ( $atbAvant == $atbApres ) return array(false,"ATB non augmenté par TailWind de Bernard");
		
		// Check Combat::apply_change_ATB() avec valeur négative
		$combat->set_turn_context(2,0);
		$skill = $baretta->get_skill(1);
		$atbAvant = $combat->get_speedCounter()[0];
		// Le sort d'attaque a 15% de ne pas passer : on se donne 3 chances de l'appliquer
		$cpt = 0;
		do{
			$combat->apply_skill_actions($skill);
			$atbApres = $combat->get_speedCounter()[0];
			$cpt++;
		}while ( $atbAvant == $atbApres && $cpt < 3 );
		if ( $atbAvant == $atbApres ) return array(false,"ATB non réduit par Turbulence de Baretta");
		
		
		return array(true,"OK");
	}
	
	//Verifie le mécanisme PrecisionAttaquant / ResistanceDefenseur
	function tf_combat_buffDebuff(){
		
		// Runage
		$runeCode = "";
		for ( $i=0; $i<129; $i++ )	$runeCode .= mt_rand(0,1);
		
		// Monstres
		$bernard = MonsterLib::load_monster("Bernard",$runeCode);
		$bernard->set_fightStats(array(10000,500,500,100,0,0,100,0,10000));
		$vero1 = MonsterLib::load_monster("Veromos",$runeCode);
		$vero1->set_fightStats(array(10000,500,500,100,0,0,100,0,10000));
		$vero2 = MonsterLib::load_monster("Veromos",$runeCode);
		$vero2->set_fightStats(array(10000,500,500,100,0,0,100,0,10000));
		$bella = MonsterLib::load_monster("Belladeon",$runeCode);
		$bella->set_fightStats(array(10000,500,500,100,0,0,100,0,10000));
		
		// Equipes & combat
		$team1 = array($vero1);
		$team2 = array($vero2);
		$combat = new Combat($team1,$team2,false);
		
		// Check Combat::apply_debuff()
		$combat->set_turn_context(2,0);
		$skill = $vero2->get_skill(1);
		$cpt = 0;
		do{
			$combat->apply_skill_actions($skill);
			$cpt++;
		}while ($cpt<5 && count($combat->get_teams()[1][0]->get_debuffs())==0);
		$d = $combat->get_teams()[1][0]->get_debuffs();
		if ( count($d) == 0 ) return array(false,"Erreur debuff : aucun debuff posé");
		
		// Check Combat::apply_cleanse()
		$combat->set_turn_context(1,0);
		$skill = $vero1->get_skill(2);
		$combat->apply_skill_actions($skill);
		$d = $combat->get_teams()[1][0]->get_debuffs();
		if ( count($d) != 0 ) return array(false,"Erreur cleanse : ".count($d)." debuff restant");
		
		
		$team1 = array($bella);
		$team2 = array($bernard);
		$combat = new Combat($team1,$team2,false);
		
		// Check Combat::apply_buff()
		$combat->set_turn_context(2,0);
		$skill = $bernard->get_skill(2);
		$combat->apply_skill_actions($skill);
		$b = $combat->get_teams()[2][0]->get_buffs();
		if ( count($b) == 0 ) return array(false,"Erreur buff : aucun buff posé");
		
		// Check Combat::apply_strip()
		$combat->set_turn_context(1,0);
		$skill = $bella->get_skill(1);
		$cpt = 0;
		do{
			$combat->apply_skill_actions($skill);
			$cpt++;
		}while ($cpt<5 && count($combat->get_teams()[2][0]->get_buffs())>0);
		$b = $combat->get_teams()[2][0]->get_buffs();
		if ( count($b) != 0 ) return array(false,"Erreur strip : ".count($b)." buff restant");
		
		return array(true,"OK");
	}
	
	//Verifie le mécanisme retirant un monstre du combat après sa mort
	function tf_combat_dieInAction(){
		
		// Runage
		$runeCode = "";
		for ( $i=0; $i<129; $i++ )	$runeCode .= mt_rand(0,1);
		
		// Monstres
		$vero = MonsterLib::load_monster("Veromos",$runeCode);
		$vero->set_fightStats(array(10000,1000,400,100,0,0,100,0,10000));
		$lush = MonsterLib::load_monster("Lushen",$runeCode);
		$lush->set_fightStats(array(1,1000,200,100,0,0,0,0,10000));
		
		// Equipes & combat
		$team1 = array($vero);
		$team2 = array($lush);
		$combat = new Combat($team1,$team2,false);
		
		// Contexte : Vero lance 2 mega smash (deg constants)
		$combat->set_turn_context(1,0);
		$skill = $vero->get_skill(0);
		$combat->apply_skill_actions($skill);
		
		// Test
		$t = $combat->get_teams();
		if ( $t[2][0] != null )		return array(false,"L equipe 2 devrait etre vide");
		$d = $combat->get_deads();
		if ( count($d[2]) != 1 )	return array(false,"Les morts de l equipe 2 devraient compter 1 membre");
		
		return array(true,"OK");
	}
	
	
	//Lance un combat
	function tm_combat(){
		
		$runeCode = "";
		for ( $i=0; $i<224; $i++ )	$runeCode .= mt_rand(0,1);
		$vero = MonsterLib::load_monster("Veromos",$runeCode);
		$barreta = MonsterLib::load_monster("Baretta",$runeCode);
		$bella1 = MonsterLib::load_monster("Belladeon",$runeCode);
		$lushen = MonsterLib::load_monster("Lushen",$runeCode);
		$bernard = MonsterLib::load_monster("Bernard",$runeCode);
		$bella2 = MonsterLib::load_monster("Belladeon",$runeCode);
		$team1 = array($vero,$barreta,$bella1);
		$team2 = array($lushen,$bernard,$bella2);
		$combat = new Combat($team1,$team2);
		
		$combat->run();
		$winner = $combat->get_winner();
		
		if ( $winner == 1 || $winner == 2 )
			return array(true,"OK");
		else
			return array(false,"Pas de vainqueur désigné : $winner");
	}

?>