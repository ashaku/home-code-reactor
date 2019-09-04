<?
	include ("constants.php");
	include ("Skill.php");
	include ("Rune.php");
	
	class Monster{
		
		private $name;
		private $element;			// ELMT
		private $baseStats;			// array(STATS)
		private $runes;				// Runage
		private $runeModifiers;		// array(STATS)
		private $runeStats;			// baseStat + runeModifiers + leadSkill : default stats at start of fight
		private $buffList;			// array(BUFF)
		private $debuffList;		// array(DEBUFF)
		private $buffModifiers;		// array(STATS)
		//private $skillModifiers;	// array(STATS)
		private $fightStats;		// runeStat + buffModifiers
		private $skills;			// Array(SKILL)
		private $leaderSkill;		// Bonus
		private $cooldowns;			// array(int)
		
		function __construct($aNom,$aElement,$aStats,$aCodeRunes,$aSkills,$aLead) {
			
			// copy given datas
			$this->name = $aNom;
			if ( $aElement >= 0 && $aElement <= 5 ){
				$this->element = $aElement;
			}
			if ( sizeof($aStats) == 8 ){
				$this->baseStats = $aStats;
			}
			$this->cooldowns = array();
			for ( $i=0; $i<=3; $i++ ){
				if ( $aSkills[$i] ){
					$this->skills[$i] = $aSkills[$i];
					$this->cooldowns[$i] = 0;
				}
			}
			$this->leaderSkill = $aLead;
			
			// Calculate rune stats modifiers
			$runage = new Runage($aCodeRunes);
			$this->runes = $runage;
			$this->runeModifiers = array();
			$this->buffModifiers = array();
			$this->skillModifiers = array();
			$this->calculate_runes_modifiers();
			
			// Init other members
			$this->buffList = array();
			$this->debuffList = array();
			$this->fightStats = $this->runeStats;
			$this->fightStats[8] = $this->runeStats[0];
		}
		
		// Getters
		public function get_name(){				return $this->name;				}
		public function get_element(){			return $this->element;			}
		public function get_base_stats(){		return $this->baseStats;		}
		public function get_base_stat($stat){	return $this->baseStats[$stat];	}
		public function get_runes(){			return $this->runes;			}
		public function get_skills(){			return $this->skills;			}
		public function get_skill($id){			return $this->skills[$id];		}
		public function get_leadSkill(){		return $this->leaderSkill;		}
		public function get_runes_mod(){		return $this->runeModifiers;	}
		public function get_runes_stats(){		return $this->runeStats;		}
		public function get_runes_stat($stat){	return $this->runeStats[$stat];	}
		public function get_fight_stats(){		return $this->fightStats;		}
		public function get_fight_stat($stat){	return $this->fightStats[$stat];}
		public function get_buffs(){			return $this->buffList;			}
		public function get_debuffs(){			return $this->debuffList;		}
		public function get_buff_mod(){			return $this->buffModifiers;	}
		public function get_cooldowns(){		return $this->cooldowns;		}
		
		// Aggregate bonus from runage (family + sub)
		private function calculate_runes_modifiers(){
			$this->runeModifiers = array(0,0,0,0,0,0,0,0);
			// Each runageSubBonus
			$i = 0;
			foreach($this->runes->get_stats_modifiers() as $mod){
				$s = floor($i/2);
				if ( $i % 2 == 0 || $s > 3 ){
					$this->runeModifiers[$s] += $mod;
				}else{
					$pc = round( $mod * $this->baseStats[$s] / 100 );
					$this->runeModifiers[$s] += $pc;
				}
				$i++;
			}
			// Each runageFamilyBonus
			$i = 0;
			foreach($this->runes->get_family_modifiers() as $mod){
				$s = floor($i/2);
				if ( $i % 2 == 0 || $i > 3 ){
					$this->runeModifiers[$s] += $mod;
				}else{
					$pc = round( $mod * $this->baseStats[$s] / 100 );
					$this->runeModifiers[$s] += $pc;
				}
				$i++;
			}
			
			// Update runeStats (base + runes)
			for ( $i=0; $i<8; $i++ ){
				$v = $this->baseStats[$i] + $this->runeModifiers[$i];
				if ( ($i==STATS::CR || $i==STATS::PRE || $i==STATS::RES) && $v > 100 ) $v = 100;
				$this->runeStats[$i] = $v;
				$this->buffModifiers[$i] = 0;
			}
		}
		
		
		public function display(){
			echo "
				<div class='monsterID'>
					<b>".$this->name."</b><br>
					<span>(".ELMT::get_name($this->element).")</span>
					<br><br>
					<table cellspacing=0 border=0 width=100%>";
			// Stats
			for ($i=0;$i<4;$i++){
				echo "
					<tr>
						<td title='".STATS::get_long_name($i)."' class='statName'>".STATS::get_short_name($i)."</td><td class='statVal'>".$this->baseStats[$i]."</td><td class='statMod'>+".$this->runeModifiers[$i]."</td>
						<td title='".STATS::get_long_name($i+4)."' class='statName'>".STATS::get_short_name($i+4)."</td><td class='statVal'>";
						$v = $this->baseStats[$i+4]+$this->runeModifiers[$i+4];
						if ( $i != 1 && $v > 100 ) $v = 100;
						echo "$v</td><td class='statMod'>&nbsp;</td>
					</tr>";
			}
			echo "
					</table>
					<br><br>";
			
			// Skills
			echo "
					<div class='skills'>";
			foreach ( $this->skills as $skill){
				echo "
						<span title='".$skill->get_desc()."'>".$skill->get_name()."</span>";
			}
			echo "
					</div><br><br>";
			
			// Runes
			$this->runes->display();
			echo"
				</div>";
		}
		public function display_infight($speedBar){
			$currentHP = $this->fightStats[STATS::HP];
			$maxHP = $this->fightStats[STATS::MAXHP];
			$perCentHP = round( $currentHP * 100 / $maxHP );
			$mainStats = "";
			foreach ( $this->runes->get_main_stats() as $st ){	$mainStats .= STATS::get_short_name($st). " / ";	}
			$mainStats = substr($mainStats,0,-3);
			$families = "";
			foreach ( $this->runes->get_families() as $family ){$families .= RUNEFAMILY::get_name($family)." "; }
			$buffsdebuffs = "";
			foreach ( $this->debuffList as $debuff ){	$buffsdebuffs .= "<div class='debuff' title='".DEBUFF::get_name($debuff["type"])."'>".$debuff["turns"]."</div>";	}
			foreach ( $this->buffList as $buff ){		$buffsdebuffs .= "<div class='buff' title='".BUFF::get_name($buff["type"])."'>".$buff["turns"]."</div>";	}
			
			$statsInfo = "Buff Mod ";
			foreach ( $this->buffModifiers as $k=>$v){
				$statsInfo .= STATS::get_short_name($k).":".$v.", ";
			}
			$statsInfo .= "\nStats ";
			foreach ( $this->fightStats as $k=>$v){
				$statsInfo .= STATS::get_short_name($k).":".$v.", ";
			}
			$statsInfo .= "\n\nSkills :";
			for ( $i=0; $i<4; $i++ ){
				if ( $this->skills[$i] != null ){
					$statsInfo .= "     ".str_replace("'","",$this->skills[$i]->get_name())." : ".$this->cooldowns[$i];
				}
			}
			
			echo "<div class='infightMonster ".ELMT::get_name($this->element)."' title='".$statsInfo."'>";
			echo "	<div class='name'>".$this->name."</div>";
			echo "	<div class='runesBonus'>".$families."<br>".$mainStats."</div>";
			
			echo "	<div class='buffDebuff'>".$buffsdebuffs."</div>";
			
			echo "	<table cellspacing=1><tr><td class='remainingHP' width='".$perCentHP."%' title='$currentHP / $maxHP'></td><td class='emptyBar' title='$currentHP / $maxHP'></td></tr></table>";
			//echo "	<tr><td colspan=2 style='font-size:11px;'>".$currentHP." / ".$this->maxHP."</td></tr></table>";
			
			if ( $speedBar > 100 ) $speedBarG = 100; else $speedBarG = $speedBar;
			echo "	<table cellspacing=1><tr><td class='AttackBar' width='".$speedBarG."%' title='$speedBar / 100'></td><td class='emptyBar'></td></tr></table>";
			
			echo "</div>";
		}
		
		
		// The leader skill bonus is aggregated to runes bonus. FightStats are recalculated
		// This function should be called at fight start, so we can assume there are no buff modifiers and fightStat = runeStat
		public function add_leader_skill_bonus ( $leadSkill, $zone=LeaderSkill::ZONE_ARENA ){
			
			// Check condition
			if ( $leadSkill->applies($zone, $this->element) ) {
			
				// Decode bonus
				$b = $leadSkill->get_bonus();
				$leadSkillStat = $b->get_stat();
				$leadSkillType = $b->get_type();
				$leadSkillValue = $b->get_value();
				if ( $leadSkillType == 1 ){
					$statDelta = round($this->get_base_stat($leadSkillStat) * $leadSkillValue / 100);
				}else{
					$statDelta = $leadSkillValue;
				}
				
				// Calculate new stat value
				$v = $this->runeStats[$leadSkillStat] + $statDelta;
				if ( ($leadSkillStat==STATS::CR || $leadSkillStat==STATS::PRE || $leadSkillStat==STATS::RES) && $v > 100 ) $v = 100;
				
				// Update stat value
				$this->runeStats[$leadSkillStat] = $v;
				$this->fightStats[$leadSkillStat] = $v;
				if ( $leadSkillStat == STATS::HP ){
					$this->fightStats[8] = $v;
				}
				return true;
			}
			return false;
		}
		
		
		// Choose a skill
		// You can specify skill index (advanced user) or let the monster choose one available skill at random
		public function choose_skill($skillId=null){
			if ( $skillId == null ) $skillId = $this->choose_skill_id();
			$this->cooldowns[$skillId] = $this->skills[$skillId]->get_cooldown();
			return $this->skills[$skillId];
		}
		public function choose_skill_id(){
			
			// PROVOKE HERE
			if ( $this->is_provoked() > -1 ) return 0;
			
			// List available skills and choose one
			$tabSkill = array();
			foreach ( $this->cooldowns as $id => $cd ){
				if ( $cd==0 && $this->skills[$id]->is_active() ){
					array_push($tabSkill,$id);
				}
			}
			$nbSkillAvailable = count($tabSkill);
			if ( $nbSkillAvailable > 1 ){
				return $tabSkill[mt_rand(0,$nbSkillAvailable-1)];
			}else{
				return $tabSkill[0];
			}
		}
		
		
		// Decrease skills & (de)buffs cooldown
		public function cooldown(){
			$this->cooldown_skills();
			$this->cooldown_buff_debuff();
		}
		// Modify cooldowns of monster. Parameter describe the number of turns to add or remove
		public function cooldown_skills($nb=-1){
			foreach ( $this->cooldowns as $k=>$v ){
				$new_v = $v + $nb;
				if ( $new_v < 0 ) $new_v = 0;
				$this->cooldowns[$k] = $new_v;
				//echo "<br>[Monster::cooldown_skills($nb)] indic $k: valeur $v => $new_v";
			}
		}
		// Decrease all current buff and debuff  
		public function cooldown_buff_debuff(){
			
			// Buffs
			$tmp_buffs = array();
			foreach ( $this->buffList as $k => $buff ){
				if ( $buff["turns"] > 1 ){
					$buff["turns"]--;
					//echo "<br>[cooldown] buff ".BUFF::get_name($buff["type"])." : ".($buff["turns"]+1)." => ".($buff["turns"]);
					array_push($tmp_buffs,$buff);
				}else{
					//echo "<br>[cooldown] buff ".BUFF::get_name($buff["type"])." : supprime";
					$this->update_buffModifiers(0,1,$buff);
				}
			}
			$this->buffList = $tmp_buffs;
			
			// Debuffs
			$tmp_debuffs = array();
			foreach ( $this->debuffList as $debuff ){
				if ( $debuff["turns"] > 1 ){
					$debuff["turns"]--;
					//echo "<br>[cooldown] debuff ".DEBUFF::get_name($debuff["type"])." : ".($debuff["turns"]+1)." => ".($debuff["turns"]);
					array_push($tmp_debuffs,$debuff);
				}else{
					
					// TODO : apply bomb effect here
					
					//echo "<br>[cooldown] debuff ".DEBUFF::get_name($debuff["type"])." : supprime";
					$this->update_buffModifiers(0,0,$debuff);
				}
			}
			$this->debuffList = $tmp_debuffs;
		}
		
		
		// When the monster is hit. Return true if he's still alive after
		public function take_hit ( $dmg ){
			
			// TODO : check for invincibility buff
			
			$this->fightStats[STATS::HP] -= $dmg;
			if ( $this->fightStats[STATS::HP] > 0 ) return true;
			return false;
		}
		
		// When monster receive heal. Return false if heal didn't apply
		public function heal($amount){
			
			// Check for noheal debuff
			foreach($this->get_debuffs() as $mydebuff){
				if ( $mydebuff["type"]==DEBUFF::NOHEAL )	return false;
			}
			
			$this->fightStats[STATS::HP] += $amount;
			if ( $this->fightStats[STATS::HP] > $this->fightStats[STATS::MAXHP] ) 
				$this->fightStats[STATS::HP] = $this->fightStats[STATS::MAXHP];
			
			return true;
		}
		
		
		// BUFF
		public function add_buff($buff){
			// Check if buff is already on monster => don't add but refresh nbTurns
			foreach($this->get_buffs() as $k=>$mybuff){
				if ( $mybuff["type"] == $buff["type"] ){
					$this->buffList[$k]["turns"] = $buff["turns"];
					return true;
				}
			}
			// Check number of buffs. If more than 10 => ignore
			if ( count($this->buffList) <= 10 ){
				array_push($this->buffList,$buff);
				
				// If new buff is about stats : update fightStats
				$this->update_buffModifiers(1,1,$buff);
			}
		}
		
		// DEBUFF
		public function add_debuff($debuff){
			// Check if debuff is already on monster => don't add but refresh nbTurns (except for CONST DMG)
			foreach($this->get_debuffs() as $k=>$mydebuff){
				if ( $mydebuff["type"]==$debuff["type"] && $debuff["type"]!=DEBUFF::CONSTDMG ){
					$this->debuffList[$k]["turns"] = $debuff["turns"];
					return true;
				}
			}
			// Check number of debuffs. If more than 10 => replace older debuff by new one (except for CONST DMG)
			if ( count($this->debuffList)>9 && $debuff["type"]!=DEBUFF::CONSTDMG ){
				//array_shift($this->debuffList);
			}
			
			// Add debuff on monster
			array_push($this->debuffList,$debuff);
			
			// If new debuff is about stats : update buff modifiers (and fightStats)
			$this->update_buffModifiers(1,0,$debuff);
		}
		
		// CLEANSE
		public function remove_debuff(){
			if ( count($this->debuffList) > 0 ){
				$debuff = array_shift($this->debuffList);
				$this->update_buffModifiers(0,0,$debuff);
				return true;
			}
			return false;
		}
		
		// STRIP
		public function remove_buff(){
			if ( count($this->buffList) > 0 ){
				$buff = array_shift($this->buffList);
				$this->update_buffModifiers(0,1,$buff);
				return true;
			}
			return false;
		}
		
		
		
		// When buffing/debuffing : update buff modifiers and fightStats
		// $add : 	0:removed ; 1:added
		// isBuff : 0:debuff ; 1:buff
		private function update_buffModifiers ( $add, $isBuff, $buffOrDebuff){
			if ( $add ){
				if ($isBuff){
					// buff was added : add according value
					switch($buffOrDebuff["type"]){
						case BUFF::RAISEATK : 	$this->set_buffModifier(STATS::ATK,$this->get_runes_stat(STATS::ATK) * 0.5); break;
						case BUFF::RAISEDEF : 	$this->set_buffModifier(STATS::DEF,$this->get_runes_stat(STATS::DEF) * 0.7); break;
						case BUFF::RAISESPD : 	$this->set_buffModifier(STATS::SPD,$this->get_runes_stat(STATS::SPD) * 0.3); break;
						case BUFF::RAISECR : 	$this->set_buffModifier(STATS::CR,30); break;
					}
				}else{
					// debuff was added : substract according value
					switch($buffOrDebuff["type"]){
						case DEBUFF::LOWATK : 	$this->set_buffModifier(STATS::ATK,$this->get_runes_stat(STATS::ATK) * -0.5); break;
						case DEBUFF::LOWDEF : 	$this->set_buffModifier(STATS::DEF,$this->get_runes_stat(STATS::DEF) * -0.7); break;
						case DEBUFF::LOWSPD : 	$this->set_buffModifier(STATS::SPD,$this->get_runes_stat(STATS::SPD) * -0.3); break;
					}
				}
			}else{
				if ($isBuff){
					// buff was removed : substract what it needs to get back to original stat
					switch($buffOrDebuff["type"]){
						case BUFF::RAISEATK : 	$this->set_buffModifier(STATS::ATK,$this->get_runes_stat(STATS::ATK) * -0.5); break;
						case BUFF::RAISEDEF : 	$this->set_buffModifier(STATS::DEF,$this->get_runes_stat(STATS::DEF) * -0.7); break;
						case BUFF::RAISESPD : 	$this->set_buffModifier(STATS::SPD,$this->get_runes_stat(STATS::SPD) * -0.3); break;
						case BUFF::RAISECR : 	$this->set_buffModifier(STATS::CR,-30); break;
					}
				}else{
					// debuff was removed : add what it needs to get back to original stat
					switch($buffOrDebuff["type"]){
						case DEBUFF::LOWATK : 	$this->set_buffModifier(STATS::ATK,$this->get_runes_stat(STATS::ATK) * 0.5); break;
						case DEBUFF::LOWDEF : 	$this->set_buffModifier(STATS::DEF,$this->get_runes_stat(STATS::DEF) * 0.7); break;
						case DEBUFF::LOWSPD : 	$this->set_buffModifier(STATS::SPD,$this->get_runes_stat(STATS::SPD) * 0.3); break;
					}
				}
			}
			
			
			// Pour Chilling : SPD+9 for each buff
			if ( $this->name == "Chilling" ){
				$this->fightStats[STATS::SPD] = $this->runeStats[STATS::SPD] + $this->buffModifiers[STATS::SPD] + sizeof($this->buffList)*9;
			}
			
		}
		
		// Update the buff modifiers array
		public function set_buffModifier ( $stat, $value ){
			//echo "<br>update ".STATS::get_long_name($stat)." by ".$value;
			$newModifier = $this->buffModifiers[$stat] + round($value);
			$this->buffModifiers[$stat] = $newModifier;
			$newStat = $this->runeStats[$stat] + $newModifier;
			if ( ($stat==STATS::CR || $stat==STATS::PRE || $stat==STATS::RES) && $newStat > 100 ) $newStat = 100;
			$this->fightStats[$stat] = $newStat;
		}
		
		
		
		// Check if monster has constant damage debuff and apply it
		public function apply_constant_damages(){
			$nbConstDmg = 0;
			foreach($this->get_debuffs() as $debuff){
				if ( $debuff["type"] == DEBUFF::CONSTDMG ){
					$this->fightStats[STATS::HP] -= round($this->fightStats[STATS::MAXHP] * 0.05);
					$nbConstDmg++;
				}
			}
			return $nbConstDmg;
		}
		
		// Check if monster has heal buff and apply it
		public function apply_heal_buff(){
			foreach($this->get_buffs() as $buff){
				if ( $buff["type"] == BUFF::HEAL ){
					
					// Check if mob has unrecoverable debuff
					foreach($this->get_debuffs() as $mydebuff){
						if ( $mydebuff["type"]==DEBUFF::NOHEAL )	return false;
					}
					
					$maxHP = $this->fightStats[STATS::MAXHP];
					$heal = round($maxHP * 0.15);
					$newHP = $this->fightStats[STATS::HP] + $heal;
					if ( $newHP > $maxHP )	$newHP = $maxHP;
					$this->fightStats[STATS::HP] = $newHP;
					return true;
				}
			}
			return false;
		}
		
		// Check if monster is Stun, asleep or frozen
		public function isReady(){
			foreach($this->get_debuffs() as $debuff){
				if ( $debuff["type"]==DEBUFF::STUN || $debuff["type"]==DEBUFF::ASLEEP || $debuff["type"]==DEBUFF::FREEZE ){
					return false;
				}
			}
			return true;
		}
	
		// Return ID of provoking monster or -1 if not provoked
		public function is_provoked(){
			foreach($this->get_debuffs() as $mydebuff){
				if ( $mydebuff["type"]==DEBUFF::PROVOKE )	return $mydebuff["src"];
			}
			return -1;
		}
	
		// TESTING FUNCTIONS /!\
		public function set_fightStat($stat,$val){		$this->fightStats[$stat] = $val;	}
		public function set_fightStats($stats){			$this->fightStats = $stats;			}
	}
	
	class LeaderSkill{
		
		// Constant values to handle the condition of leader skill
		// reminder : We'll also use ELMT class constants (0~5), so we don't want to mix numbers
		const NONE = 10;
		const ZONE = 11;
		const ELMT = 12;
		const ZONE_ARENA = 20;
		const ZONE_DUNGEON = 21;
		const ZONE_GUILD = 22;
		
		private $statBonus;	// class Bonus object
		private $condition;	// array(type,val) type 0:none 1:zone 2:elmt
		
		function __construct($aBonus,$aCondition){
			$this->statBonus = $aBonus;
			$this->condition = $aCondition;
		}
		
		public function get_bonus(){ return $this->statBonus; }
		
		// Check if condition applies to given zone and monster elmt
		public function applies($zone,$elmt){
			switch ( $this->condition[0] ){
				case $this::NONE : return true;
				case $this::ZONE : if ( $zone == $this->condition[1] ) return true; else return false;
				case $this::ELMT : if ( $elmt == $this->condition[1] ) return true; else return false;
			}
		}
	}
	
	// Monster Factory : just ask by name and you will receive
	abstract class MonsterLib{
		public function load_monster($name,$runeCode){
			switch ( $name ){
				case "Veromos":
					$element = ELMT::dark;
					$megaSmash = SkillLib::load_skill("MegaSmash");
					$superCrush = SkillLib::load_skill("SuperCrush");
					$convMagic = SkillLib::load_skill("ConversionOfMagic");
					$skills = array($megaSmash,$superCrush,$convMagic);
					$leadBonus = new Bonus(STATS::HP,1,33);
					$leadCondition = array(LeaderSkill::NONE,0);
					$leaderSkill = new LeaderSkill($leadBonus,$leadCondition);
					$stats = array(9225,769,758,100,15,50,25,15);
					break;
					
				case "Belladeon":
					$element = ELMT::light;
					$sratch = SkillLib::load_skill("Scratch");
					$seize = SkillLib::load_skill("Seize");
					$mobilize = SkillLib::load_skill("Mobilize");
					$skills = array($sratch,$seize,$mobilize);
					$leaderSkill = null;
					$stats = array(9885,472,681,108,15,50,0,15);
					break;
					
				case "Lushen":
					$element = ELMT::wind;
					$flyingCards = SkillLib::load_skill("flyingCards");
					$surpriseBox = SkillLib::load_skill("surpriseBox");
					$amputationOfMagic = SkillLib::load_skill("amputationOfMagic");
					$skills = array($flyingCards,$surpriseBox,$amputationOfMagic);
					$leadBonus = new Bonus(STATS::ATK,1,33);
					$leadCondition = array(LeaderSkill::ZONE,LeaderSkill::ZONE_DUNGEON);
					$leaderSkill = new LeaderSkill($leadBonus,$leadCondition);
					$stats = array(9225,900,461,103,15,50,0,15);
					break;
					
				case "Bernard":
					$element = ELMT::wind;
					$snatch = SkillLib::load_skill("Snatch");
					$bodySlam = SkillLib::load_skill("BodySlam");
					$tailWind = SkillLib::load_skill("TailWind");
					$skills = array($snatch,$bodySlam,$tailWind);
					$leadBonus = new Bonus(STATS::ATK,1,30);
					$leadCondition = array(LeaderSkill::ELMT,ELMT::wind);
					$leaderSkill = new LeaderSkill($leadBonus,$leadCondition);
					$stats = array(10380,417,703,111,15,50,0,15);
					break;
					
				case "Baretta":
					$element = ELMT::fire;
					$spiritThrow = SkillLib::load_skill("SpiritThrow");
					$turbulence = SkillLib::load_skill("Turbulence");
					$phoenixFury = SkillLib::load_skill("PhoenixFury");
					$skills = array($spiritThrow,$turbulence,$phoenixFury);
					$leadBonus = new Bonus(STATS::SPD,1,19);
					$leadCondition = array(LeaderSkill::NONE,0);
					$leaderSkill = new LeaderSkill($leadBonus,$leadCondition);
					$stats = array(11205,681,549,105,15,50,0,15);
					break;
					
				case "Mav":
					$element = ELMT::wind;
					$honorAttack = SkillLib::load_skill("HonorAttack");
					$declareWar = SkillLib::load_skill("DeclareWar");
					$wingsOfWind = SkillLib::load_skill("WingsOfWind");
					$skills = array($honorAttack,$declareWar,$wingsOfWind);
					$leadBonus = new Bonus(STATS::HP,1,30);
					$leadCondition = array(LeaderSkill::ELMT,ELMT::wind);
					$leaderSkill = new LeaderSkill($leadBonus,$leadCondition);
					$stats = array(7500,468,363,105,15,50,0,15);
					break;
					
				case "Chilling":
					$element = ELMT::water;
					$trickster = SkillLib::load_skill("Trickster");
					$nightWind = SkillLib::load_skill("NightWind");
					$cunning = SkillLib::load_skill("Cunning");
					$skills = array($trickster,$nightWind,$cunning);
					$leadBonus = new Bonus(STATS::PRE,1,40);
					$leadCondition = array(LeaderSkill::ZONE,LeaderSkill::ZONE_DUNGEON);
					$leaderSkill = new LeaderSkill($leadBonus,$leadCondition);
					$stats = array(9225,736,626,101,15,50,0,15);
					break;
		
			}
			return new Monster($name,$element,$stats,$runeCode,$skills,$leaderSkill);
		}
	}
?>