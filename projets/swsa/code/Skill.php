<?
	// Skill Classes : Represent any monster skill
	// Beside name and cooldown, the skill is mostly a list of actions
	// Actions are in charge of targetting and handle a set of effects
	// Effects are atomic skill action (deal damage, heal, buff, etc). Theey are splitted in two categories : damage and other
	include ("skillFactory.php");
	
	// Global object Skill : name, list of effects and cooldown
	class Skill{
		private $name;
		private $actions;	// SkillAction
		private $cooldown;	// int
		private $passive;	// enum
	
		function __construct($aNom,$aActions,$aCooldown,$aPassive) {
			$this->name = $aNom;
			$this->actions = $aActions;
			$this->cooldown = $aCooldown;
			$this->passive = $aPassive;
		}
		public function get_name(){			return $this->name;		}
		public function get_actions(){		return $this->actions;	}
		public function get_cooldown(){		return $this->cooldown;	}
		public function is_active(){		return ( $this->passive == EVT::NONE ) ? true : false;		}
		public function get_passive_event(){	return $this->passive;	}
		public function get_desc(){
			$pres = "";
			if ( !$this->is_active() ) $pres = "(passive)\n";
			foreach ( $this->actions as $action ){
				$pres.= $action->get_desc()."\n";
			}
			$pres .= "cooldown : ".$this->cooldown." turn";
			if ( $this->cooldown > 1 ) $pres.= "s";
			return $pres;
		}
	}
	
	// General Skill Action : targetting infos
	class SkillAction{
		private $targetZone;	// 0:unique ; 1:multi ; 2:all
		private $targetSide; 	// 0:ennemy ; 1:ally
		private $targetNbMulti;	// if zone=multi
		private $effects;		// SkillEffect[]
		private $applyRandomEffect = false;
		
		
		function __construct($aEffects,$aZone,$aSide,$aNbMulti=0) {
			//$this->type = $aType;
			$this->targetZone = $aZone;
			$this->targetSide = $aSide;
			$this->targetNbMulti = $aNbMulti;
			$this->effects = $aEffects;
		}
		
		// Getter
		public function get_targetSide(){		return $this->targetSide;			}
		public function get_targetZone(){		return $this->targetZone;			}
		public function get_targetNbMulti(){	return $this->targetNbMulti;		}
		public function apply_random(){			$this->applyRandomEffect = true;	}
		public function get_effects(){
			if ( $this->applyRandomEffect ){
				foreach ( $this->effects as $id=>$effect ){
					if ( $this->effects[$id]->get_type() == EFFECT::damage ){
						$idDmg = $id;
						break;
					}
				}
				do{	$id = mt_rand(0,count($this->effects)-1);
				}while ( $id == $idDmg );
				return array($this->effects[$idDmg],$this->effects[$id]);
			}else{
				return $this->effects;
			}
		}
		public function get_desc(){
			$pres = "";
			if ( $this->applyRandomEffect ) $pres = "random : ";
			foreach ( $this->effects as $effect ){
				$t = $effect->get_type();
				if ( $t == EFFECT::buff ){
					$pres .= "[".BUFF::get_name($effect->get_data()["type"])."]";
					
				}elseif ( $t == EFFECT::debuff ){
					$pres .= "[".DEBUFF::get_name($effect->get_data()["type"])."]";
					
				}elseif ( $t == EFFECT::damage ){
					$pres .= "[damage]";
					$n = $effect->get_nbHits();
					if ( $n > 1 )	$pres .= " x".$n;
					if ( $effect->ignore_def() ) $pres .= " ignore def";
					
				}else{
					$pres .= "[".EFFECT::get_name($t)."]";
				}
				$pres .= ", ";
			}
			$pres = substr ( $pres, 0, -2 );
			$pres .= " on ";
			switch ( $this->targetZone ){
				case TARGET::UNIQUE : 	$pres .= "1"; break;
				case TARGET::ALL : 		$pres .= "all"; break;
				case TARGET::MULTI : 	$pres .= $this->targetNbMulti; break;
				case TARGET::SELF : 	$pres .= "self"; break;
			}
			if ( $this->targetZone != TARGET::SELF ){
				$pres .= " ".TARGET::get_name($this->targetSide);
			}
			return $pres;
		}
	}
	
	// Generic Skill effect : take care of mandatory type of effect
	abstract class SkillEffect{
		private $type;			// SKILLEFFECT
		function __construct($aType) {		$this->type = $aType;		}
		public function get_type(){			return $this->type;			}
	}
	
	
	// Specific effect Damage
	class Damage extends SkillEffect{
		private $formula;
		private $nbHits;
		private $ignoreDef;
		
		function __construct($aFormula,$aNbHits=1,$aIgnoreDef=false){
			parent::__construct(EFFECT::damage);
			$this->formula = $aFormula;
			$this->nbHits = $aNbHits;
			$this->ignoreDef = $aIgnoreDef;
		}
		
		public function get_nbHits(){	return $this->nbHits;		}
		public function ignore_def(){	return $this->ignoreDef;	}
		
		public function get_damage($attackingMonsterStats){
			// decode formula
			$operand1 = 0;
			$operand2 = 0;
			$operator1 = "";
			$operator2 = "";
			$total = 0;
			//echo "<br><br>Start Decode Formula : ";print_r($this->formula);
			foreach($this->formula as $i => $line){
				//echo "<br> &nbsp; line $i : ";print_r($line);
				
				// for operand
				if ( $i % 2 == 0 ){
					
					// If unique value
					if ( count($line) == 1 ){
						// decode stat
						$data = $line[0];
						//echo "<br> &nbsp; Unique value : ".$data;
						if ( !is_numeric($data) ){
							switch($data){
								case "HP": $lineVal = $attackingMonsterStats[STATS::HP];	break;
								case "ATK": $lineVal = $attackingMonsterStats[STATS::ATK];	break;
								case "DEF": $lineVal = $attackingMonsterStats[STATS::DEF];	break;
								case "SPD": $lineVal = $attackingMonsterStats[STATS::SPD];	break;
								case "MAXHP": $lineVal = $attackingMonsterStats[STATS::MAXHP];	break;
							}
							//echo " => ".$lineVal;
						}else{
							$lineVal = $data;
						}
						
						
					// If operand is formula itself : apply same logic on a second level
					}else{
						
						$lineVal = 0;
						//echo "<br> &nbsp; Array value : compute";
						foreach($line as $j => $data){
							//echo "<br> &nbsp; &nbsp; $j. $data";
							
							if ( $j % 2 == 0 ){
								//operand
								//decode stat
								if ( !is_numeric($data) ){
									switch($data){
										case "HP": $numval = $attackingMonsterStats[STATS::HP];		break;
										case "ATK": $numval = $attackingMonsterStats[STATS::ATK];	break;
										case "DEF": $numval = $attackingMonsterStats[STATS::DEF];	break;
										case "SPD": $numval = $attackingMonsterStats[STATS::SPD];	break;
										case "MAXHP": $numval = $attackingMonsterStats[STATS::MAXHP];	break;
									}
									//echo " => ".$numval;
								}else{
									$numval = $data;
								}
								if ( $j == 0 ){
									//store first
									$lineVal = $numval;
									//echo " :: Store $numval as first subline value";
								}else{
									//calculate next
									//echo " :: Compute $lineVal ";
									switch ($operator2){
										case "+": $lineVal += $numval; break;
										case "-": $lineVal -= $numval; break;
										case "*": $lineVal *= $numval; break;
										case "/": $lineVal /= $numval; break;
										case "%": $lineVal = $lineVal * $numval / 100; break;
									}
									//echo " => $lineVal";
								}
							}else{
								$operator2 = $data;
								//echo " :: update operator2";
							}
						}
					}
					
					// If first value
					if ( $i == 0 ){
						
						// store
						$total = $lineVal;
						//echo "<br> &nbsp; Store $lineVal as first line value";
					}else{
						
						// For next values : update total with last operand and current operand
						//echo "<br> &nbsp; Compute $total ";
						switch ($operator1){
							case "+": $total += $lineVal; break;
							case "-": $total -= $lineVal; break;
							case "*": $total *= $lineVal; break;
							case "/": $total /= $lineVal; break;
							case "%": $total = $total * $lineVal / 100; break;
						}
						//echo " => $total";
					}
				
				// for operator
				}else{
					// simply store it
					$operator1 = $line[0];
					//echo " update operator1";
				}
			}
			//echo "<br> => total damage : ".round ( $total );
			return round ( $total );
		}
	}
	
	
	// Generic class for specific effect
	// used for every effects except deal_damage
	class SingleEffect extends SkillEffect{
		private $data;	// data about effect to store and give back
		function __construct($aEffectType,$aData){
			parent::__construct($aEffectType);
			$this->data = $aData;
		}
		public function get_data(){			return $this->data;		}
	}
	
?>