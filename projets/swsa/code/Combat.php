<?
	// TODO : Explain what Combat class is for
	// Basically run a fight, which is a succession of turns
	// A turn is a monster launching a skill => Get highest attackBar & choose skill
	// apply skill effects : choose target(s)
	// teams are stored in array "teams" at indexes 1 and 2
	// dead mobs go to an array "teamDeads" (same principle as teams, same index too)
	
	
	class Combat{
		
		private $numTurn;
		private $teams;
		private $speedCounter;
		private $teamDeads;
		private $verbose;
		
		private $idPlayingMonster;
		private $playingMonster;
		private $playingSide;
		private $targetMonsters;
		private $targetSide;
		
		function __construct($aTeam1,$aTeam2,$aVerbose=false) {
			$this->teams[1] = $aTeam1;
			$this->teams[2] = $aTeam2;
			$this->speedCounter = array();
			$this->teamDeads = array();
			$this->verbose = $aVerbose;
			//$this->run();
		}
		
		// Start the fight
		public function run(){
			
			$this->numTurn = 0;
			$this->apply_leader_skill();
			
			// TODO : APPLY shield RUNE EFFECT HERE
			
			$watchdog = 0;
			$bothTeamAlive = true;
			while ( $bothTeamAlive && $this->numTurn < 199 && $watchdog < 999 ){
				
				$this->tick();
				
				// 1 monster has reached 100 attack bar : choose and apply one of its skill for this turn
				$this->numTurn ++;
				if ( $this->verbose ){
					echo "<div class='fight'><table><tr><td style='font-size:16px;'><b>".$this->numTurn."</b></td></tr><tr><td>";
					$this->display_team(1); echo "</td></tr><tr><td>"; $this->display_team(2); echo "</td></tr><tr><td style='text-align:left;margin-left:8px;'>";
				}
				$this->select_playing_monster();
				$this->play_turn();
				if ( $this->verbose ){	echo "</td></tr></table></div><br><br>";	}
				
				// check if one team is dead
				for ( $idTeam=1; $idTeam<=2; $idTeam++ ){				
					if ( count($this->teams[$idTeam]) == count($this->teamDeads[$idTeam]) ) $bothTeamAlive = false;
				}
				
				$watchdog++;
			}
		}
		
		// Apply leader skill of monster #0 of each team
		public function apply_leader_skill(){
			
			$zone = LeaderSkill::ZONE_ARENA;
			for ( $idTeam=1; $idTeam<=2; $idTeam++ ){
				if ( $leadSkill = $this->teams[$idTeam][0]->get_leadSkill() ){
					foreach($this->teams[$idTeam] as $id=>$mon){
						if ( $mon->add_leader_skill_bonus($leadSkill,$zone) && $this->verbose ){
							echo "Team $idTeam monster #$id : Applied leader bonus ".$leadSkill->get_bonus()->toString()."<br>";
						}
						
						// init speed counters
						$this->speedCounter[$id+(10*($idTeam-1))] = 0;
						
					}
				}
			}
		}
		
		// Increase attack bar for each monster of each team untill one reach 100
		public function tick(){
			$playTurn = false;
			do{
				for ( $idTeam=1; $idTeam<3; $idTeam++ ){
					foreach ( $this->teams[$idTeam] as $id => $mon ){
						if ( $mon != null ){
							$idATB = $id+(10*($idTeam-1));
							$tick = $mon->get_fight_stat(STATS::SPD) * 0.07;
							$newVal = $this->speedCounter[$idATB] + $tick;
							$this->speedCounter[$idATB] = $newVal;
							if ( $newVal > 100 ) $playTurn = true;
						}
					}
				}
			}while(!$playTurn);
			return;
		}
		
		// Select fastest monster to play this turn
		public function select_playing_monster(){
			
			// find next monster to play
			$this->playingSide = 0;
			arsort($this->speedCounter);
			foreach($this->speedCounter as $k => $spd){	$fastest = $k;	break;	}
			$this->speedCounter[$k] = 0;	// Reinit speed bar of playing monster
			if ( $k > 9 ){
				$idMonster = $k-10;
				$this->playingSide = 2;
			}else{
				$idMonster = $k;
				$this->playingSide = 1;
			}
			$this->playingMonster = $this->teams[$this->playingSide][$idMonster];
			if ( $this->verbose ) echo "Team ".$this->playingSide.", monster ".$idMonster." ".$this->playingMonster->get_name()." ";
			
			//return $idMonster;
			$this->idPlayingMonster = $idMonster;
		}
		
		// Apply buff/debuff, passive, cooldown and choose a skill if not stun
		private function play_turn(){
			
			// Apply effects of constant damages and heal
			$this->apply_buff_debuff();
			
			// Execute monster passive skill
			foreach ( $this->playingMonster->get_skills() as $id => $skill ){
				if ( !$skill->is_active() && $this->playingMonster->get_cooldowns()[$id]==0 ){
					$this->apply_skill_actions($skill);
				}
			}
			
			// Check if monster is Stun, asleep or frozen
			if ( $this->playingMonster->isReady() ){
			
				//////////////// MONSTER PLAY ITS TURN ////////////
				$this->playingMonster->cooldown_skills();
				
				// Choose Active skill to execute (will update skill cooldown)
				$skill = $this->playingMonster->choose_skill();
				
				// Execute each actions of skill
				$this->apply_skill_actions($skill);
				
				// APPLY violent RUNE EFFECT HERE
				foreach ( $this->playingMonster->get_runes()->get_families() as $f ){
					if ( $f == RUNEFAMILY::Violent ){
						while ( mt_rand(1,100) < 22 ){
							if ( $this->verbose ) echo "<br><br>[VIOLENT] Play another turn !";
							$this->play_turn($idMonster);
						}
						break;
					}
				}
				
			}elseif ( $this->verbose ){
				echo " Stun, Frozen or Asleep";
			}
			
			// Finally, cooldaown buffs and debuffs
			$this->playingMonster->cooldown_buff_debuff();
		}
		
		private function apply_buff_debuff(){
			
			// Apply debuff:constDamage
			if ( $nb = $this->playingMonster->apply_constant_damages() ){
				if ( $this->verbose ) echo "(CONST DMG -".round($nb*0.05*$this->playingMonster->get_fight_stat(STATS::MAXHP)).") ";
				if ( $this->playingMonster->get_fight_stat(STATS::HP) <= 0 ){
					$this->remove_monster($this->idPlayingMonster,$this->playingSide);
					if ( $this->verbose ) echo " => dead";
					return;
				}
			}
			// Apply buff:heal
			if ( $this->playingMonster->apply_heal_buff() ){
				if ( $this->verbose ) echo "(HEAL +".round($this->playingMonster->get_fight_stat(STATS::MAXHP)*0.15).") ";
			}
		}
		
		
		
		// Choose target of action and decode action effects
		public function apply_skill_actions($skill){
			if ( $this->verbose ){
				echo "<br>use ";
				if ( !$skill->is_active() ) echo "passive ";
				echo "skill [".$skill->get_name()."]";
			}
			
			foreach ( $skill->get_actions() as $action ){
				$this->choose_targets($action);
			
				foreach ( $action->get_effects() as $effect ){
					
					$effectType = $effect->get_type();
					if ( $this->verbose ) echo "<br> &nbsp; [".EFFECT::get_name($effectType)."] on target team ".$this->targetSide;
					
					if ( $effectType == EFFECT::damage ){
						
						// Apply damages to target(s)
						$dmg = $effect->get_damage($this->playingMonster->get_fight_stats());
						$ignoreDef = $effect->ignore_def();
						$nbHits = $effect->get_nbHits();
						$isCritical = ( mt_rand(0,100) < $this->playingMonster->get_fight_stat(STATS::CR) ) ? true : false;
						$nbOpponents = count($this->targetMonsters);
						$nbKill = 0;
						foreach ( $this->targetMonsters as $k=>$idTarget ){
							for ($i=0; $i<$nbHits; $i++ ){
								if ( $this->targetMonsters[$k] > -1 ){
									if( $this->deal_damage ( $dmg, $idTarget, $ignoreDef, $isCritical ) ){
										
										// monster is dead : remove it from target(s)
										$this->targetMonsters[$k] = -1;
										$nbKill++;
										
										// TODO : APPLY auto-revive effect HERE
										
									}else{
										// Monster survived
										// TODO : APPLY vampire, despair, revenge RUNE EFFECTS HERE
										
										// APPLY passive skills triggered on hit HERE
									}
								}
							}
						}
						
						// No need to apply debuffs on dead bodies ^_^'
						if ( $nbKill == $nbOpponents ) return;
						
					}else{
						
						// Apply effect on target(s)
						$data = $effect->get_data();
						foreach ( $this->targetMonsters as $target ){
							if ( $target > -1 ){
								switch ( $effectType ){
									case EFFECT::debuff :			$this->apply_debuff ( $data, $target );				break;
									case EFFECT::buff :				$this->apply_buff ( $data, $target );				break;
									case EFFECT::strip :			$this->apply_strip ( $data, $target );				break;
									case EFFECT::cleanse :			$this->apply_cleanse ( $data, $target );			break;
									case EFFECT::heal :				$this->apply_heal ( $data, $target );				break;
									case EFFECT::raiseAtkBar :		$this->apply_change_ATB ( $data, $target );			break;
									case EFFECT::lowAtkBar :		$this->apply_change_ATB ( $data*-1, $target );		break;
									case EFFECT::raisecooldown :	$this->apply_change_cooldown ( $data, $target );	break;
									case EFFECT::lowcooldown :		$this->apply_change_cooldown ( $data*-1, $target );	break;
								}
							}
						}
					}
				}
			}
		}
		
		// Fill the targetMonsters array according to target Side and Zone
		public function choose_targets($action){
			
			// Get targets side (ally/ennemy)
			$this->targetMonsters = array();
			if ( $action->get_targetSide() == TARGET::ALLIES ){
				$this->targetSide = $this->playingSide;
			}else{
				$this->targetSide = $this->playingSide % 2 + 1;
				
				// PROVOKE HERE
				$provokingMonsterID = $this->playingMonster->is_provoked();
				if ( $provokingMonsterID > -1 ){
					array_push($this->targetMonsters,$provokingMonsterID);
					if ( $this->verbose ) echo " PROVOKE ENNEMY #".$provokingMonsterID;
					return;
				}
			}
			
			// Get targets zone (unique,multi,all)
			$targetZone = $action->get_targetZone();
			
			// Choose corresponding monster(s)
			switch($targetZone){
				case TARGET::UNIQUE :	// Select one random monster among alive members of targeted team
										$nbMon = count($this->teams[$this->targetSide]);
										$watchdog = 0;
										do{
											$id_target = mt_rand(0,$nbMon-1);
											$watchdog++;
										}while ( $this->teams[$this->targetSide][$id_target]==null && $watchdog<19);
										if ( $watchdog<19 ) array_push($this->targetMonsters,$id_target);
										break;
										
				case TARGET::MULTI :	// Select N random targets among alive members of targeted team (same monster can be selected multiple times)
										$nb = $action->get_targetNbMulti();
										$nbMon = count($this->teams[$this->targetSide]);
										for ( $i=0; $i<$nb; $i++ ){
											$watchdog = 0;
											do{
												$id_target = mt_rand(0,$nbMon-1);
												$watchdog++;
											}while ( $this->teams[$this->targetSide][$id_target]==null && $watchdog<19);
											if ( $watchdog<19 ) array_push($this->targetMonsters,$id_target);
										}
										break;
										
				case TARGET::ALL :		// Copy all alive monsters in targeted team
										foreach ( $this->teams[$this->targetSide] as $id=>$mon ){
											if ( $mon != null )	array_push($this->targetMonsters,$id);
										}
										break;
										
				case TARGET::SELF :		// Copy self ID in targeted team
										array_push($this->targetMonsters,$this->idPlayingMonster);
										break;
			}
		}
		
		


		// Next functions will finally apply a specific effect to a specific monster
		////////////////////////////////////////////////////////////////////////////
		
		// Calculate final damages, apply them to def monster and return :
		// - true if the monster survived
		// - false if the monster is killed
		public function deal_damage ( $dmg, $idMonster, $ignoreDef, $isCritical ){
			
			if ( $this->verbose ) echo "<br> &nbsp; &nbsp; on monster #".$idMonster." dmg=".$dmg;
			
			// Critical ?
			if ( $isCritical ){
				$dmg *= ( 1 + $this->playingMonster->get_fight_stat(STATS::CD) / 100 );
				if ( $this->verbose ) echo " +".$this->playingMonster->get_fight_stat(STATS::CD)."% crit";
			}
			
			// Elemental advantage ?
			$adv = $this->has_advantage ( $this->playingMonster->get_element(), $this->teams[$this->targetSide][$idMonster]->get_element() );
			if ( $adv == 1){
				$dmg *= 1.3;
				if ( $this->verbose ) echo " +30% elmt";
			}elseif ( $adv == -1){
				$dmg *= 0.7;
				if ( $this->verbose ) echo " -30% elmt";
			}
			
			// Defense
			if ( $ignoreDef )	$ennemyDef = 0;
			else				$ennemyDef = $this->teams[$this->targetSide][$idMonster]->get_fight_stat(STATS::DEF);
			$defFactor = 1000 / (1140 + 3.5*$ennemyDef);
			$dmg = round($dmg*$defFactor);
			if ( $this->verbose ) echo " x".round($defFactor,2)." def => !".$dmg."!" ;
			
			// Hit
			if ( $this->teams[$this->targetSide][$idMonster]->take_hit($dmg) ){
				// Monster survived damamges
				
				////////////////////////////////////////////////////////////////////////////////
				// TODO : check for counter-attack (if target has revenge rune, or counter buff)
				////////////////////////////////////////////////////////////////////////////////
				return false;
			}else{
				// Monster is dead
				if ( $this->verbose ) echo " => dead";
				$this->remove_monster($idMonster,$this->targetSide);
				return true;
			}
		}
		
		// Add debuff on target monster
		private function apply_debuff($debuff,$idMonster){
			if ( $this->effect_applies($this->playingMonster->get_fight_stat(STATS::PRE),$this->teams[$this->targetSide][$idMonster]->get_fight_stat(STATS::RES),$debuff["perCent"]) ){
				if ( $this->verbose ) echo "<br> &nbsp; &nbsp; ".DEBUFF::get_name($debuff["type"])." on monster #".$idMonster." for ".$debuff["turns"]." turns";
				$debuff["src"] = $this->idPlayingMonster;
				$this->teams[$this->targetSide][$idMonster]->add_debuff($debuff);
			}elseif ( $this->verbose ) echo "<br> &nbsp; &nbsp; monster #".$idMonster." resist ".DEBUFF::get_name($debuff["type"]);
		}
		
		// remove buff(s) from target
		private function apply_strip($nbBuffToStrip, $idMonster){
			if ( $this->effect_applies($this->playingMonster->get_fight_stat(STATS::PRE),$this->teams[$this->targetSide][$idMonster]->get_fight_stat(STATS::RES),100) ){
				if ( $nbBuffToStrip == 0 ){
					//strip all
					while ( $this->teams[$this->targetSide][$idMonster]->remove_buff() ){
						if ( $this->verbose ) echo "<br> &nbsp; &nbsp; Strip monster #".$idMonster;
					}
				}else{
					// Strip N
					for($i=0;$i<$nbBuffToStrip;$i++){
						if ( $this->teams[$this->targetSide][$idMonster]->remove_buff() ){
							if ( $this->verbose ) echo "<br> &nbsp; &nbsp; Strip monster #".$idMonster;
						}else{
							// no buff to strip
							return;
						}
					}
				}
			}elseif ( $this->verbose ) echo "<br> &nbsp; &nbsp; monster #".$idMonster." resist strip";
		}
		
		// add buff to target(s)
		private function apply_buff($buff, $idMonster){
			if ( $this->verbose ) echo "<br> &nbsp; &nbsp; ".BUFF::get_name($buff["type"])." on monster #".$idMonster." for ".$buff["turns"]." turns";
			$this->teams[$this->targetSide][$idMonster]->add_buff($buff);
		}
		
		// remove oldest debuff from target
		private function apply_cleanse($nbDebuffToClean, $idMonster){
			for ( $i=0; $i<$nbDebuffToClean; $i++ ){
				$cleansed = $this->teams[$this->targetSide][$idMonster]->remove_debuff();
				if ( $cleansed ){
					if ( $this->verbose )echo "<br> &nbsp; &nbsp; Clanse monster #".$idMonster;
				}else{
					// no debuff to cleanse
					return;
				}
			}
		}
		
		// heal target(s) HP
		private function apply_heal($perCentHeal, $idMonster){
			$amount = $this->teams[$this->targetSide][$idMonster]->get_fight_stat(STATS::MAXHP) * $perCentHeal / 100;
			$healed = $this->teams[$this->targetSide][$idMonster]->heal($amount);
			if ( $healed && $this->verbose )	echo "<br> &nbsp; &nbsp; Heal monster #".$idMonster." for ".$perCentHeal."% ($amount)";
		}
		
		// Raise or Lower target(s) Attack bar
		private function apply_change_ATB($perCentRaise, $idMonster){
			$id = $idMonster + 10*($this->targetSide-1);
			$v = $this->speedCounter[$id];
			if ( $perCentRaise == -100 ){
				$v = 0;
			}else{
				$v += $perCentRaise;
				if ( $v < 0 ) $v = 0;
			}
			$this->speedCounter[$id] = $v;
			if ( $this->verbose )	echo "<br> &nbsp; &nbsp; monster #".$idMonster." ATB move by ".$perCentRaise."% => $v";
		}
		
		
		// Raise or Lower target(s) skills cooldown
		private function apply_change_cooldown($nbTurns, $idMonster){
			$this->teams[$this->targetSide][$idMonster]->cooldown_skills($nbTurns);
			if ( $this->verbose )	echo "<br> &nbsp; &nbsp; monster #".$idMonster." cooldowns move by ".$nbTurns." turns";
		}
		
		
		
		
		
		
		// UTILITY FUNCTIONS
		//////////////////////////////
		public function has_advantage($elmt1,$elmt2){
			//echo "has_advantage ( ".ELMT::get_name($elmt1).", ".ELMT::get_name($elmt2)." )<br>";
			if (
				($elmt1 == ELMT::water && $elmt2 == ELMT::fire) ||
				($elmt1 == ELMT::fire && $elmt2 == ELMT::wind) ||
				($elmt1 == ELMT::wind && $elmt2 == ELMT::water) ||
				($elmt1 == ELMT::dark && $elmt2 == ELMT::light) ||
				($elmt1 == ELMT::light && $elmt2 == ELMT::dark)
			){
				return 1;
			}elseif (
				($elmt1 == ELMT::water && $elmt2 == ELMT::wind) ||
				($elmt1 == ELMT::fire && $elmt2 == ELMT::water) ||
				($elmt1 == ELMT::wind && $elmt2 == ELMT::fire)
			){
				return -1;
			}else{
				return 0;
			}
		}
		
		// When a monster dies => move it from "team" array to "dead" array
		private function remove_monster($id,$team){
			$mon = $this->teams[$team][$id];
			$this->teamDeads[$team][$id] = $mon;
			$this->teams[$team][$id] = null;
			$this->speedCounter[$id+(10*($team-1))] = 0;
		}
		
		// Compare attacker accuracy and defender resistance
		public function effect_applies($attackPre,$defRes,$effectRate){
			// Check Immunity
			/*foreach ( $defMonster->get_buff() as $buff ){
				if ( $buff["type"] == BUFF::IMMUNITY ) return false;
			}*/
			// Check PRE/RES
			$chanceToResist = $defRes - $attackPre;
			if ( $chanceToResist < 15 ) $chanceToResist = 15;
			// Check effect rate
			$chanceToApply = (100-$chanceToResist) * $effectRate / 100;
			if ( mt_rand(1,100) < $chanceToApply ) return true;
			return false;
		}
		
		public function get_winner(){
			for ( $idTeam=1; $idTeam<3; $idTeam++ ){
				if ( count($this->teams[$idTeam]) == count($this->teamDeads[$idTeam]) ) return $idTeam % 2 + 1;
			}
			return 0;
		}
		
		public function display_team ( $team ){
			echo "<div class='team'>";
			
			$nbMon = count($this->teams[$team]) + count($this->teamDeads[$team]);
			for ( $i=0; $i<$nbMon; $i++ ){
				$mon = $this->teams[$team][$i];
				if ( $mon != null ){
					$mon->display_infight($this->speedCounter[$i+($team-1)*10]);
				}else{
					echo "<div class='infightMonster'><table style='opacity:0;'><tr><td>&nbsp;</td></tr></table></div>";
				}
			}
			echo "</div>";
		}
	
		public function display(){
			echo "<div class='fight'><table><tr><td>";
			$this->display_team(1);
			echo "</td></tr><tr><td style='text-align:center'>VS</td></tr><tr><td>";
			$this->display_team(2);
			echo "</td></tr></table></div>";
		}
		
		
		
		
		// TESTING FUNCTIONS /!\
		// Define wich team and monster is up to play next
		public function set_turn_context($playingSide, $playingMonster){
			$this->playingSide = $playingSide;
			$this->idPlayingMonster = $playingMonster;
			$this->playingMonster = $this->teams[$this->playingSide][$this->idPlayingMonster];
		}
		public function get_teams(){			return $this->teams;			}
		public function get_deads(){			return $this->teamDeads;		}
		public function get_speedCounter(){ 	return $this->speedCounter; 	}

		public function get_playingMonster_speedcounterID(){	return $this->idPlayingMonster + ($this->playingSide-1)*10;		}
		public function get_targets(){							return array ( $this->targetSide, $this->targetMonsters );		}
	}
	
	
	
?>