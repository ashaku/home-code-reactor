<? 
	// VISUALISATION DES TESTS
	ini_set('max_execution_time', 0);
	require('../code/AlgoGen.php');
	require('tests.php');
?>
<!DOCTYPE html>
<html lang='fr'>
	<head>
		<title>SWSA</title>
		<link rel="stylesheet" type="text/css" href="../code/screen.css">
		<meta charset="utf-8">
	</head>
	<body>
	
<?
	$testRune = 0;
	$testRunage = 0;
	$testEncode = 0;
	$testMon = 0;
	$testSkill = 0;
	$testFight = 0;
	$testAlgoGen = 0;
	$testAutoLoad = 0;
	
	$genericTest = 1;
	
	
	if ( $genericTest ){
		
		// Runage
		$runeCode = "";
		for ( $i=0; $i<129; $i++ )	$runeCode .= mt_rand(0,1);
		
		// Monstres
		$bernard = MonsterLib::load_monster("Bernard",$runeCode);
		$vero = MonsterLib::load_monster("Veromos",$runeCode);
		$lushen = MonsterLib::load_monster("Lushen",$runeCode);
		$bella = MonsterLib::load_monster("Belladeon",$runeCode);
		$mav = MonsterLib::load_monster("Mav",$runeCode);
		$baretta = MonsterLib::load_monster("Baretta",$runeCode);
		
		// Equipes & combat
		$team1 = array($mav,$bernard,$vero);
		$team2 = array($lushen,$bella,$baretta);
		$combat = new Combat($team1,$team2,true);
		$combat->run();
		
	}
	
	
	if ( $testAlgoGen ){
		$taille_population = 50;
		$nb_generations = 10;
		$verbose = false;
		echo "<div class='algogen'>";
		$a = new AlgoGenSWSA (
								$taille_population,
								$nb_generations,
								$verbose
							);
		echo "</div>";
	}
	
	if ($testSkill){
	
		$runeCode = "";
		$mains = "11100110";	// SPD CR PRE
		$rune1 = "000000000000000100000011000001000000";	// Energy / Atq+ / HP+ HP+% Atq+% Def+
		$rune2 = "000001010000100100001011000011010000";	// Energy / Vit+ / Def+% CR+% CD+% Pre+%
		$rune3 = "110011110000000000000001000000100000";	// Fatal / Def+ / Res+% HP+ HP+% Atq+
		$rune4 = "110000110000010000000101000001100000";	// Fatal / CR+% / Atq+% Def+ Def+% Vit+
		$rune5 = "110010010000101100001101000011110000";	// Fatal / HP+ / CR+% CD+% Pre+% Res+%
		$rune6 = "110000010000001000000011000001010000";	// Fatal / Pre+% / HP+% Atq+ Atq+% Def+%
		//$runeCode = $mains.$rune1.$rune2.$rune3.$rune4.$rune5.$rune6;
		// Aleatoire
		for ( $i=0; $i<224; $i++ )	$runeCode .= mt_rand(0,1);
		
		$m = MonsterLib::load_monster("Belladeon",$runeCode);
		$fightStats = $m->get_fight_stats();
		
		// Display
		$m->display();
		$skills = $m->get_skills();
		
		$scratch = $skills[0];
		echo "<br><br>".$scratch->get_name()." : ";
		$effect = $scratch->get_effects()[0];
		$res = $effect->execute($fightStats);
		echo "<br>".$res[0]." dmg";
		echo "<br>can apply ".DEBUFF::get_name($res[2])." for ".$res[3]." turns with ".$res[1]."%";
		
		$seize = $skills[1];
		echo "<br><br>".$seize->get_name()." : ";
		$effect = $seize->get_effects()[0];
		$res = $effect->execute($fightStats);
		echo "<br>".$res[0]." dmg";
		echo "<br>strip ".$res[1]." buffs";
		
		$mobilize = $skills[2];
		echo "<br><br>".$mobilize->get_name()." : ";
		$effect = $mobilize->get_effects()[0];
		$res = $effect->execute();
		echo "<br>heal for  ".$res."%";
		
	}
	
	if ( $testRune ){
		$position = 2;
		$family = RUNEFAMILY::Energy;
		$runeMain = STATS::HP;
		for ( $i=0; $i<18; $i++ )	$runeCode .= mt_rand(0,1);
		$runeCode = "000011011011011011";
		$rune = new Rune($position,$family,$runeMain,$runeCode);
		$rune->display_code();
		echo "<br>";
		$rune->display();
	}
	
	if ( $testRunage ){
		
		// Runage test
		$mains = "11011011";	// SPD CR PRE
		$families = "0000011000000";	// 4 fatale 2 energy
		$rune1 = "100101110111";	// CR CD Pre Res
		$rune2 = "100101110111";	// CR CD Pre Res
		$rune3 = "000001010011";	// HP Atq Def Vit
		$rune4 = "000001010011";	// HP Atq Def Vit
		$rune5 = "100101110111";	// CR CD Pre Res
		$rune6 = "000001010011";	// HP Atq Def Vit
		//$runageCode = $mains.$families.$rune1.$rune2.$rune3.$rune4.$rune5.$rune6;
		$runageCode = "";
		for ( $i=0; $i<129; $i++ )	$runageCode .= mt_rand(0,1);
		
		echo "<br><br>RUNAGE TEST<br>";
		//echo "Code : ".$runageCode."<br>";
		$r = new Runage($runageCode);
		$r->display();
	}

	if ( $testMon ){
		
		$runeCode = "";
		$mains = "11011011";	// SPD CR PRE
		$families = "0000000100000";	// 4 fatale 2 energy
		$rune1 = "100101110111100101";	// CR CD Pre Res CR CD
		$rune2 = "100101110111100101";	// CR CD Pre Res CR CD
		$rune3 = "000001010011000001";	// HP Atq Def Vit HP Atq
		$rune4 = "000001010011000001";	// HP Atq Def Vit HP Atq
		$rune5 = "100101110111100101";	// CR CD Pre Res CR CD
		$rune6 = "000001010011000001";	// HP Atq Def Vit HP Atq
		$runeCode = $mains.$families.$rune1.$rune2.$rune3.$rune4.$rune5.$rune6;
		// Aleatoire
		//for ( $i=0; $i<36; $i++ )	$runeCode .= mt_rand(0,1);
		
		$vero = MonsterLib::load_monster("Veromos",$runeCode);
		$vero->display();

		
		echo "<u>Base Stats :</u> "; print_r ($vero->get_base_stats());
		echo "<br>Runes Modifiers : "; print_r ($vero->get_runes_mod());
		echo "<br>Runes Stats : "; print_r ($vero->get_runes_stats());
		echo "<br>Fight Stats : "; print_r($vero->get_fight_stats());
		
		// lead skill
		$leadSkill = $vero->get_leadSkill();
		echo "<br><br><u>Leader SKill :</u> ";
		print_r($leadSkill);
		$vero->add_leader_skill_bonus($leadSkill);
		echo "<br>Runes Stats : "; print_r ($vero->get_runes_stats());
		echo "<br>Fight Stats : "; print_r($vero->get_fight_stats());
		

		//take_hit
		$dmg = 5992;
		echo "<br><br><u>Take hit :</u> $dmg damages";
		$vero->take_hit($dmg);
		echo "<br>Fight Stats : "; print_r($vero->get_fight_stats());
		
		// BUFF / DEBUFF
		//add debuff on stats
		$debuffAtk = array("perCent"=>50,"type"=>DEBUFF::LOWATK,"turns"=>2);
		$debuffDef = array("perCent"=>25,"type"=>DEBUFF::LOWDEF,"turns"=>3);
		$debuffSpd = array("perCent"=>75,"type"=>DEBUFF::LOWSPD,"turns"=>1);
		echo "<br><br><u>Add LOWATK, LOWDEF, LOWSPD debuffs :</u>";
		echo "<br>debuff : "; print_r($vero->get_debuffs());
		echo "<br>buff modifiers : "; print_r($vero->get_buff_mod());
		$vero->add_debuff($debuffAtk);
		$vero->add_debuff($debuffDef);
		$vero->add_debuff($debuffSpd);
		echo "<br>debuff : "; print_r($vero->get_debuffs());
		echo "<br>buff modifiers : "; print_r($vero->get_buff_mod());
		echo "<br>Fight Stats : "; print_r($vero->get_fight_stats());
		
		//cleanse (remove debuff)
		echo "<br><br><u>Cleanse 1 :</u> ";
		$vero->remove_debuff();
		echo "<br>debuff : "; print_r($vero->get_debuffs());
		echo "<br>buff modifiers : "; print_r($vero->get_buff_mod());
		echo "<br>Fight Stats : "; print_r($vero->get_fight_stats());
		echo "<br><br><u>Cleanse 2 :</u> ";
		$vero->remove_debuff();
		echo "<br>debuff : "; print_r($vero->get_debuffs());
		echo "<br>buff modifiers : "; print_r($vero->get_buff_mod());
		echo "<br>Fight Stats : "; print_r($vero->get_fight_stats());
		echo "<br><br><u>Cleanse 3 :</u> ";
		$vero->remove_debuff();
		echo "<br>debuff : "; print_r($vero->get_debuffs());
		echo "<br>buff modifiers : "; print_r($vero->get_buff_mod());
		echo "<br>Fight Stats : "; print_r($vero->get_fight_stats());
		
		//add buff
		$buffAtk = array("perCent"=>50,"type"=>BUFF::RAISEATK,"turns"=>2);
		$buffSpd = array("perCent"=>25,"type"=>BUFF::RAISESPD,"turns"=>3);
		$buffDef = array("perCent"=>75,"type"=>BUFF::RAISEDEF,"turns"=>1);
		$buffCr = array("perCent"=>75,"type"=>BUFF::RAISECR,"turns"=>1);
		echo "<br><br><u>Add RAISEATK, RAISESPD, RAISEDEF, RAISECR buffs :</u>";
		echo "<br>buff : "; print_r($vero->get_buffs());
		echo "<br>buff modifiers : "; print_r($vero->get_buff_mod());
		$vero->add_buff($buffAtk);
		$vero->add_buff($buffDef);
		$vero->add_buff($buffSpd);
		$vero->add_buff($buffCr);
		echo "<br>buff : "; print_r($vero->get_buffs());
		echo "<br>buff modifiers : "; print_r($vero->get_buff_mod());
		echo "<br>Fight Stats : "; print_r($vero->get_fight_stats());
		
		//strip (del buff)
		echo "<br><br><u>Strip 1 :</u> ";
		$vero->remove_buff();
		echo "<br>buff : "; print_r($vero->get_buffs());
		echo "<br>buff modifiers : "; print_r($vero->get_buff_mod());
		echo "<br>Fight Stats : "; print_r($vero->get_fight_stats());
		echo "<br><br><u>Strip 2 :</u> ";
		$vero->remove_buff();
		echo "<br>buff : "; print_r($vero->get_buffs());
		echo "<br>buff modifiers : "; print_r($vero->get_buff_mod());
		echo "<br>Fight Stats : "; print_r($vero->get_fight_stats());
		echo "<br><br><u>Strip 3 :</u> ";
		$vero->remove_buff();
		echo "<br>buff : "; print_r($vero->get_buffs());
		echo "<br>buff modifiers : "; print_r($vero->get_buff_mod());
		echo "<br>Fight Stats : "; print_r($vero->get_fight_stats());
		echo "<br><br><u>Strip 4 :</u> ";
		$vero->remove_buff();
		echo "<br>buff : "; print_r($vero->get_buffs());
		echo "<br>buff modifiers : "; print_r($vero->get_buff_mod());
		echo "<br>Fight Stats : "; print_r($vero->get_fight_stats());
		
		
	}
	
	if ( $testFight ){
		
		$t1v1 = false;
		
		$stats = array(9225,769,758,100,15,50,0,15);
		$megaSmash = SkillLib::load_skill("MegaSmash");
		$superCrush = SkillLib::load_skill("SuperCrush");
		$convMagic = SkillLib::load_skill("ConversionOfMagic");
		$skills = array($megaSmash,$superCrush,$convMagic);
		$leaderSkill = new Bonus(STATS::HP,1,33);
		
		$runeCode = "";
		for ( $i=0; $i<224; $i++ )	$runeCode .= mt_rand(0,1);
		$mon1a = MonsterLib::load_monster(pickMonster(),$runeCode);
		$runeCode = "";
		for ( $i=0; $i<224; $i++ )	$runeCode .= mt_rand(0,1);
		$mon1b = MonsterLib::load_monster(pickMonster(),$runeCode);
		$runeCode = "";
		for ( $i=0; $i<224; $i++ )	$runeCode .= mt_rand(0,1);
		$mon1c = MonsterLib::load_monster(pickMonster(),$runeCode);
		
		$runeCode = "";
		for ( $i=0; $i<224; $i++ )	$runeCode .= mt_rand(0,1);
		$mon2a = MonsterLib::load_monster(pickMonster(),$runeCode);
		$runeCode = "";
		for ( $i=0; $i<224; $i++ )	$runeCode .= mt_rand(0,1);
		$mon2b = MonsterLib::load_monster(pickMonster(),$runeCode);
		$runeCode = "";
		for ( $i=0; $i<224; $i++ )	$runeCode .= mt_rand(0,1);
		$mon2c = MonsterLib::load_monster(pickMonster(),$runeCode);
		
		if ( $t1v1 ){
			$team1 = array($mon1a);
			$team2 = array($mon2a);
		}else{
			$team1 = array($mon1a,$mon1b,$mon1c);
			$team2 = array($mon2a,$mon2b,$mon2c);
		}
		
		$combat = new Combat($team1,$team2,true);
		$combat->run();
		$winner = $combat->get_winner();
		echo "<br><br> winner : Team $winner<br>";
		$combat->display_team($winner);
	}
	
	if ( $testAutoLoad ){
		for ( $i=0; $i<129; $i++ )	$runeCode .= mt_rand(0,1);
		$monsterName = "Mav";//pickMonster();
		$monster = MonsterLib::load_monster($monsterName,$runeCode);
		$monster->display();
	}
	
	
	if ( $testEncode ){
		$main246 = array(STATS::SPD,STATS::CR,STATS::ATK);
		//$families = array(RUNEFAMILY::Rage,RUNEFAMILY::Energy);
		$families = array(RUNEFAMILY::Blade,RUNEFAMILY::Energy,RUNEFAMILY::Will);
		$sub1 = array(STATS::HP,STATS::ATK,STATS::DEF,STATS::HP,STATS::ATK,STATS::CR);
		$sub2 = array(STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP);
		$sub3 = array(STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP);
		$sub4 = array(STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP);
		$sub5 = array(STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP);
		$sub6 = array(STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP,STATS::HP);
		$subs = array($sub1,$sub2,$sub3,$sub4,$sub5,$sub6);
		$runageCode = Runage::encode_runage($main246,$families,$subs);
		$check = substr($runageCode,0,1);
		if ( $check == "0" || $check == "1" ){			
			$r = new Runage($runageCode);
			$r->display();
			echo "<br><br>";;
			$r->display_code();
		}
		echo "<br><br>".$runageCode;
	}
	
	
	
	function pickMonster(){
		$mt = array("Veromos","Belladeon","Lushen","Bernard","Baretta","Mav");
		return $mt[mt_rand(0,count($mt)-1)];
	}

?>

	<body>
</html>
