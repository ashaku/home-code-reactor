<?
	// Represent a rune collection
	// Must be initialized by a binary combination
	
	// Set of 6 runes held by a monster
	class Runage{
		
		private $rawCode;	// binary code of runage
		private $runes;		// list of rune objects
		private $statsModifiers;	// Total stats modifiers (families + sub)
		private $families;		// list of family set
		private $familyModifiers;	
		
		function __construct($aCode){
			$this->rawCode = $aCode;
			$this->families = array();
			$this->decode();
			$this->statsModifiers = array();
			$this->familyModifiers = array();
			$this->calculate_stats_modifiers();
		}
	
		public function get_stats_modifiers(){	return $this->statsModifiers;	}
		public function get_family_modifiers(){	return $this->familyModifiers;	}
		public function get_families(){			return $this->families;			}
		public function get_main_stats(){
			return array($this->runes[1]->get_mainStat()->get_stat(),$this->runes[3]->get_mainStat()->get_stat(),$this->runes[5]->get_mainStat()->get_stat());
		}
		
		// Read code and save as rune stats
		// [main246][families][runeSub]
		// 22444666 F111122223333 111222333444555666 111222333444555666 111222333444555666 111222333444555666 111222333444555666 111222333444555666
		// May update code itself if runes are auto-corrected
		private function decode (){
			
			// Array of all 6 rune main stat. 1, 3, 5 are forced. 2,4,6 are described in first 8 bit of raw code [22444666][...]
			$main2 = bindec(substr($this->rawCode,0,2));
			$main4 = bindec(substr($this->rawCode,2,3)) % 5;
			if ( $main4 == 3 ) $main4 = 5;
			$main6 = bindec(substr($this->rawCode,5,3)) % 5;
			if ( $main6 > 2 ) $main6 += 3;
			$mainStats = array(-1,$main2,-1,$main4,-1,$main6);
			
			// 9th bit is type of family set : 4*2 or 2*2*2
			$familyType = substr($this->rawCode,8,1);
			// Array of all 6 runes family. 111122 or 112233
			// Also save a list of distinct famiiles of runage in private member
			$families = array();
			if ( $familyType == 0 ){
				// 4 * 2
				$rune4 = bindec(substr($this->rawCode,13,4)) % 6 + 10;
				array_push($this->families,$rune4);
				for ($i=0; $i<4; $i++ ) $families[$i] = $rune4;
				$rune2 = bindec(substr($this->rawCode,17,4)) % 9;
				array_push($this->families,$rune2);
				for ($i=4; $i<6; $i++ ) $families[$i] = $rune2;
			}else{
				// 2 * 2 * 2
				$rune1 = bindec(substr($this->rawCode,9,4)) % 9;
				array_push($this->families,$rune1);
				for ($i=0; $i<2; $i++ ) $families[$i] = $rune1;
				
				$rune2 = bindec(substr($this->rawCode,13,4)) % 9;
				array_push($this->families,$rune2);
				for ($i=2; $i<4; $i++ ) $families[$i] = $rune2;
				
				$rune3 = bindec(substr($this->rawCode,17,4)) % 9;
				array_push($this->families,$rune3);
				for ($i=4; $i<6; $i++ ) $families[$i] = $rune3;
			}
			
			// For each of 6 runes
			$update_code = false;
			for ($i=0; $i<6; $i++ ){
				
				// Create & save rune
				$runeCode = substr($this->rawCode,21+$i*18,18);
				$rune = new Rune($i+1,$families[$i],$mainStats[$i],$runeCode);
				$this->runes[$i] = $rune;
				if ( $rune->has_errors() ) $update_code = true;
			}
			if ( $update_code ){
				$oldCode = $this->rawCode;
				$newCode = substr($oldCode,0,21);
				foreach ( $this->runes as $rune ){
					$newCode .= $rune->get_code();
				}
				$this->rawCode = $newCode;
			}
		}
		
		// Will agregate statsModifiers from all 6 rune and check rune families to grant family bonus
		private function calculate_stats_modifiers(){
			
			$families = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
			$this->statsModifiers = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
			$this->familyModifiers = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
			foreach ( $this->runes as $rune ){
				// save family
				$families[$rune->get_family()]++;
				
				// Agregate rune modifiers
				$mod = $rune->get_statsModifiers();
				for ( $i=0; $i<16; $i++ ){
					$this->statsModifiers[$i] += $mod[$i];
				}
			}
			
			// Check for family bonus
			foreach($this->families as $f){
				switch ($f){
					case RUNEFAMILY::Energy :	$this->familyModifiers[1] += 15;	break;
					case RUNEFAMILY::Guard :	$this->familyModifiers[5] += 15;	break;
					case RUNEFAMILY::Blade :	$this->familyModifiers[9] += 12;	break;
					case RUNEFAMILY::Focus :	$this->familyModifiers[13] += 20;	break;
					case RUNEFAMILY::Endure :	$this->familyModifiers[15] += 20;	break;
					/*case RUNEFAMILY::Nemesis : //jauge atk+4% chaque 7%PV perdus
						//$this->familyModifiers[1] += floor($n/2) * 15;
						break;
					case RUNEFAMILY::Will :	// immune 1 turn
						//$this->familyModifiers[1] += floor($n/2) * 15;
						break;
					case RUNEFAMILY::Shield :	// Shield 15%HP 3 turns
						//$this->familyModifiers[1] += floor($n/2) * 15;
						break;
					case RUNEFAMILY::Revenge :	// counter 15%
						//$this->familyModifiers[1] += floor($n/2) * 15;
						break;
					case RUNEFAMILY::Destroy :	// pv max ennemy -4% tous les 30% degats
						//$this->familyModifiers[1] += floor($n/2) * 15;
						break;*/
						
					case RUNEFAMILY::Swift :	$this->familyModifiers[7] += 25;	break;
					case RUNEFAMILY::Rage :		$this->familyModifiers[11] += 40;	break;
					case RUNEFAMILY::Fatal :	$this->familyModifiers[3] += 35;	break;
					/*case RUNEFAMILY::Despair :	// stun +25%
						//$this->familyModifiers[11] += 40;
						break;
					case RUNEFAMILY::Vampire :	// lifeleech 35%
						//$this->familyModifiers[11] += 40;
						break;
					case RUNEFAMILY::Violent :	// extra turn 22%
						//$this->familyModifiers[11] += 40;
						break;*/
				}
			}
		}
		
		public function display(){
			
			echo "<div class='runage'>";
			$showBonus = false;
			if ( $showBonus ){
				echo "Family bonus : ";
				foreach ( $this->familyModifiers as $s => $v ){
					if ( $v > 0 ){
						echo "<span>".STATS::get_short_name(floor($s/2))."+".$v;
						if ( $s % 2 == 1 ) echo "%";
						echo "</span>";
					}
				}
				echo "<br><br>Sub bonus : ";
				foreach ( $this->statsModifiers as $s => $v ){
					if ( $v > 0 ){
						echo "<span>".STATS::get_short_name(floor($s/2))."+".$v;
						if ( $s % 2 == 1 ) echo "%";
						echo "</span>";
					}
				}
				echo "<br><br>";
			}
			
			echo "<table cellspacing=0 width=100%>
			<tr><td colspan=2>&nbsp;</td><td rowspan=3>";
			$this->runes[0]->display();
			echo "</td><td colspan=2>&nbsp;</td></tr>
			<tr><td rowspan=3>";
			$this->runes[5]->display();
			echo "</td><td>&nbsp;</td><td>&nbsp;</td><td rowspan=3>";
			$this->runes[1]->display();
			echo "</td></tr>
			<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
			<tr><td colspan=3>&nbsp;</td></tr>
			<tr><td colspan=5>&nbsp;</td></tr>";
			
			echo "<tr><td rowspan=3>";
			$this->runes[4]->display();
			echo "</td><td colspan=3>&nbsp;</td><td rowspan=3>";
			$this->runes[2]->display();
			echo "</td></tr>";
			
			echo "<tr><td>&nbsp;</td><td rowspan=3>";
			$this->runes[3]->display();
			echo "</td><td>&nbsp;</td></tr>
			<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
			<tr><td colspan=2>&nbsp;</td><td colspan=2>&nbsp;</td></tr>
			</table></div>";
			/*
			echo "<br><table>";
			for ($i=0; $i<8; $i++){
				echo "<tr><td>".STATS::get_short_name($i)."</td><td>+".$this->statsModifiers[$i*2]."</td><td>+".$this->statsModifiers[$i*2+1]."%</td></tr>";
			}
			echo "</table>";
			
			echo "<br><table>";
			for ($i=0; $i<8; $i++){
				echo "<tr><td>".STATS::get_short_name($i)."</td><td>+".$this->familyModifiers[$i*2]."</td><td>+".$this->familyModifiers[$i*2+1]."%</td></tr>";
			}
			echo "</table>";
			*/
		}
		
		public function display_code(){
			//echo "22.444.666 &nbsp; F.1111.2222.3333 &nbsp; 111.222.333.444.555.666 &nbsp; 111.222.333.444.555.666 &nbsp; 111.222.333.444.555.666 &nbsp; 111.222.333.444.555.666 &nbsp; 111.222.333.444.555.666 &nbsp; 111.222.333.444.555.666<br>";
			echo substr($this->rawCode,0,2).".".substr($this->rawCode,2,3).".".substr($this->rawCode,5,3)." &nbsp; ";
			echo substr($this->rawCode,8,1).".".substr($this->rawCode,9,4).".".substr($this->rawCode,13,4).".".substr($this->rawCode,17,4);
			for ( $i=0; $i<6; $i++ ){
				echo " &nbsp; ";
				for ( $j=0; $j<6; $j++ ){
					echo substr($this->rawCode,21+(18*$i)+(3*$j),3);
					if ( $j < 5 ) echo ".";
				}
			}
		}
		
		
		// Test-purpose : create binary code for specified runage
		public function encode_runage($main246,$families,$subs){
			
			// Main
			$nb = count($main246);
			if ( $nb != 3 ) return "erreur MainStat : $nb valeurs au lieu de 3";
			$binMainStats = "";
			$main2 = $main246[0];
			if ( $main2 > 3 ) return "erreur main2 : ".STATS::get_short_name($main2)." impossible en position 2";
			$binMainStats .= str_pad(decbin($main2),2,"0",STR_PAD_LEFT);
			$main4 = $main246[1];
			if ( $main4==3 || $main4>5 ) return "erreur main4 : ".STATS::get_short_name($main4)." impossible en position 4";
			if ( $main4 == 5 ) $main4 = 3;
			$binMainStats .= str_pad(decbin($main4),3,"0",STR_PAD_LEFT);
			$main6 = $main246[2];
			if ( $main6>2 && $main6<6 ) return "erreur main6 : ".STATS::get_short_name($main6)." impossible en position 6";
			if ( $main6 > 5 ) $main6 -= 3;
			$binMainStats .= str_pad(decbin($main6),3,"0",STR_PAD_LEFT);
			
			// Families
			if ( count($families) == 2 ){
				$binFamilies = "00000";
			}elseif ( count($families) == 3 ){
				$binFamilies = "1";
			}else{
				return "erreur : tableau families doit avoir 2 ou 3 elements";
			}
			foreach ( $families as $f ){
				if ( $f > 10 ) $f -= 10;
				$binFam = str_pad(decbin($f),4,"0",STR_PAD_LEFT);
				$binFamilies .= $binFam;
			}
			
			// subs
			$binSubs = "";
			foreach ( $subs as $subRune ){
				foreach ( $subRune as $sub ){
					$binSubs .= str_pad(decbin($sub),3,"0",STR_PAD_LEFT);
				}
			}
			
			return $binMainStats.$binFamilies.$binSubs;
		}
		
	}
	
	// Solo rune (set of bonuses)
	class Rune{
		private $code;		// 18-bit : 6 * 3-bit stat
		private $position;	// 1 ~6
		private $family;	// RUNEFAMILY
		private $mainStat; 	// Bonus
		private $subStats;	// Bonus[]
		private $defaultBonus = 5;
		private $statsModifiers;
		private $wasCorrected;
		//private $tabRunesKeys;
		//private $tabRunesErrors;
		
		
		// ============= RUNE CREATION ==============================
		function __construct($aPosition,$aFamily,$aMain,$aCode){
			$this->code = $aCode;
			$this->position = $aPosition;
			$this->family = $aFamily;
			$this->subStats = array();
			$this->wasCorrected = false;
			$this->decode($aMain);
			//$this->check_and_correct();
			$this->calculate_stats_modifiers();
		}
		
		// Read binary code and create according stats
		private function decode ($aMainStat){
			
			// Define main stat value
			switch($this->position){
				
				case 1 :	// RUNE 1 ATK
					$mainStat = STATS::ATK;
					$mainType = 0;
					$mainVal = 99;
					break;
					
				case 2 :	// RUNE 2
					$mainStat = $aMainStat;
					switch($aMainStat){
						case STATS::HP :
						case STATS::ATK :
						case STATS::DEF :
							$mainType = 1;
							$mainVal = 51;
							break;
						case STATS::SPD :
							$mainType = 0;
							$mainVal = 39;
							break;
					}
					break;
					
				case 3 :	// RUNE 3 DEF
					$mainStat = STATS::DEF;
					$mainType = 0;
					$mainVal = 99;
					break;
					
				case 4 :	// RUNE 4
					$mainStat = $aMainStat;
					$mainType = 1;
					switch($aMainStat){
						case STATS::HP :
						case STATS::ATK :
						case STATS::DEF :
							$mainVal = 51;
							break;
						case STATS::CR :
							$mainVal = 47;
							break;
						case STATS::CD :
							$mainVal = 65;
							break;
					}
					break;
					
				case 5 :	// RUNE 5 HP
					$mainStat = STATS::HP;
					$mainType = 0;
					$mainVal = 1530;
					break;
					
				case 6 :	// RUNE 6
					$mainStat = $aMainStat;
					$mainType = 1;
					$mainVal = 51;
					break;
			}
			$mainBonus = new Bonus($mainStat,$mainType,$mainVal);
			$this->set_mainStat($mainBonus);
			
			
			// Set the 6 subStat
			for ($j=0; $j<6; $j++ ){
				
				// Decode Stat and Type of bonus
				$bonusStat = bindec(substr($this->code,$j*3,3));
				$bonusType = ($bonusStat==STATS::SPD) ? 0 : 1;
				
				// If same as MainStat : change subStat
				while ( $bonusStat == $mainStat && $bonusType == $mainType ){
					$bonusStat = mt_rand(0,7);
					$this->wasCorrected = true;
				}
				// save bonus on rune
				$bonus = new Bonus($bonusStat,$bonusType,$this->defaultBonus);
				array_push($this->subStats,$bonus);
			}
			
			// If correction occured : Rewrite binary code of rune with new sub
			if ( $this->wasCorrected ){
				$newCode = "";
				foreach ( $this->subStats as $sub ){
					$stat = $sub->get_stat();
					$newCode .= str_pad(decbin($stat),3,"0",STR_PAD_LEFT);
				}
				$this->code = $newCode;
			}
		}
		
		
		
		// ============= GET / SET ==============================
		public function get_code(){				return $this->code;				}
		public function get_position(){			return $this->position;			}
		public function get_family(){			return $this->family;			}
		public function get_mainStat(){			return $this->mainStat;			}
		public function get_defaultBonus(){		return $this->defaultBonus;		}
		public function get_subStats(){			return $this->subStats;			}
		public function get_statsModifiers(){	return $this->statsModifiers;	}
		public function has_errors(){			return $this->wasCorrected;		}
		
		public function set_family($f){			$this->family = $f;				}
		public function set_mainStat($m){		$this->mainStat = $m;			}
		
		// Sum up all bonus (main + sub)
		// Results are stored in array (HP+ , HP+% , ATK+ , ATK+% , ... )
		private function calculate_stats_modifiers(){
			$this->statsModifiers = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
			
			// Main bonus
			$index = $this->mainStat->get_stat() * 2 + $this->mainStat->get_type();
			$this->statsModifiers[$index] += $this->mainStat->get_value();
			
			// Sub bonus
			foreach ( $this->subStats as $sub ){
				$index = $sub->get_stat() * 2 + $sub->get_type();
				$this->statsModifiers[$index] += $sub->get_value();
			}
		}
		
		
		
		// ============= DISPLAY ==============================
		public function display(){
			echo RUNEFAMILY::get_name($this->family)." (".$this->position.")";
			//$this->mainStat->display();
			arsort($this->statsModifiers);
			$first = true;
			foreach ($this->statsModifiers as $k=>$mod){
				if ( $mod != 0 ){
					echo "<br><span class='sub";
					if ( $first ) echo " main";
					echo "'>";
					echo STATS::get_short_name(floor($k/2))."+".$mod;
					if ( $k % 2 != 0 ) echo "%";
					echo "</span>";
					$first = false;
				}
			}
			/*if ( count($this->tabRunesErrors) > 0 )	echo "<br> ! ! !";
			echo "<br><table>";
			for ($i=0; $i<7; $i++){
				echo "<tr><td>".STATS::get_short_name($i)."</td><td>+".$this->statsModifiers[$i*2]."</td><td>+".$this->statsModifiers[$i*2+1]."%</td></tr>";
			}
			echo "</table>";*/
		}
		
		public function display_code(){
			for ( $i=0; $i<6; $i++ ) echo substr($this->code,0+3*$i,3)." ";
		}
	
	}
	
	// Solo Bonus (Stats,type,value)
	class Bonus{
		
		private $stats;
		private $type;
		private $value;
		
		function __construct($s,$t,$v){
			$this->stats = $s;
			$this->type = $t;
			$this->value = $v;
		}
		
		public function get_stat(){return $this->stats;}
		public function get_type(){return $this->type;}
		public function get_value(){return $this->value;}
		
		public function set_stat($s){$this->stats = $s;}
		public function set_type($t){$this->type = $t;}
		public function set_value($v){$this->value = $v;}
		
		public function display(){
			echo $this->toString();
		}
		
		public function toString(){
			$s = STATS::get_short_name($this->stats)." +".$this->value;
			if ( $this->type == "1" ) $s .= "%";
			return $s;
		}
	}
?>