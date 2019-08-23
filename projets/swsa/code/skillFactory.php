<?
	// Skill Factory : give the name, receive the skill object
	abstract class SkillLib{
		public function load_skill ( $name ){
			switch($name){
				
				// Veromos
				case "MegaSmash":
					$skillName = "Mega Smash";
					$dmgFormula = array(array("SPD","+",210),array("/"),array(0.7),array("%"),array("ATK"));
					$dmgEffect = SkillLib::get_damage_effect($dmgFormula);
					$debuffData = array("type"=>DEBUFF::CONSTDMG,"turns"=>2,"perCent"=>75);
					$debuffEffect = SkillLib::get_single_effect(EFFECT::debuff,$debuffData);
					$actionEffects = array($dmgEffect,$debuffEffect);
					$action = new SkillAction($actionEffects,TARGET::UNIQUE,TARGET::ENNEMY);
					$skillActions = array($action);
					$skillCooldown = 1;
					$skillActive = true;
					break;
				case "SuperCrush":
					$skillName = "Super Crush";
					$dmgFormula = array(array(16,"%","MAXHP"),array("+"),array(140,"%","ATK"));
					$dmgEffect = SkillLib::get_damage_effect($dmgFormula);
					$debuffData=array("type"=>DEBUFF::STUN,"turns"=>1,"perCent"=>60);
					$debuffEffect = SkillLib::get_single_effect(EFFECT::debuff,$debuffData);
					$actionEffects = array($dmgEffect,$debuffEffect);
					$action = new SkillAction($actionEffects,TARGET::ALL,TARGET::ENNEMY);
					$skillActions = array($action);
					$skillCooldown = 3;
					$skillActive = true;
					break;
				case "ConversionOfMagic":
					$skillName = "Conversion of magic";
					$cleanseEffect = SkillLib::get_single_effect(EFFECT::cleanse,1);
					$actionEffects = array($cleanseEffect);
					$action = new SkillAction($actionEffects,TARGET::ALL,TARGET::ALLIES);
					$skillActions = array($action);
					$skillCooldown = 1;
					$skillActive = false;
					break;
				
				// Belladeon
				case "Scratch" :
					$skillName = "Scratch";
					$dmgFormula = array(array(400),array("%"),array("ATK"));
					$dmgEffect = SkillLib::get_damage_effect($dmgFormula);
					$debuffData=array("type"=>DEBUFF::LOWDEF,"turns"=>2,"perCent"=>100);
					$debuffEffect = SkillLib::get_single_effect(EFFECT::debuff,$debuffData);
					$actionEffects = array($dmgEffect,$debuffEffect);
					$action = new SkillAction($actionEffects,TARGET::UNIQUE,TARGET::ENNEMY);
					$skillActions = array($action);
					$skillCooldown = 1;
					$skillActive = true;
					break;
				case "Seize" :
					$skillName = "Seize";
					$dmgFormula = array(array(580),array("%"),array("ATK"));
					$dmgEffect = SkillLib::get_damage_effect($dmgFormula);
					$stripEffect = SkillLib::get_single_effect(EFFECT::strip,0);
					$actionEffects = array($dmgEffect,$stripEffect);
					$action = new SkillAction($actionEffects,TARGET::UNIQUE,TARGET::ENNEMY);
					$skillActions = array($action);
					$skillCooldown = 3;
					$skillActive = true;
					break;
				case "Mobilize" :
					$skillName = "Mobilize";
					$healEffect = SkillLib::get_single_effect(EFFECT::heal,50);
					$raiseAtkBarEffect = SkillLib::get_single_effect(EFFECT::raiseAtkBar,30);
					$actionEffects = array($healEffect,$raiseAtkBarEffect);
					$action = new SkillAction($actionEffects,TARGET::ALL,TARGET::ALLIES);
					$skillActions = array($action);
					$skillCooldown = 4;
					$skillActive = true;
					break;
					
				// Lushen
				case "flyingCards" :
					$skillName = "Flying cards";
					$dmgFormula = array(array(360),array("%"),array("ATK"));
					$dmgEffect = SkillLib::get_damage_effect($dmgFormula);
					$debuffData=array("type"=>DEBUFF::NOHEAL,"turns"=>2,"perCent"=>70);
					$debuffEffect = SkillLib::get_single_effect(EFFECT::debuff,$debuffData);
					$actionEffects = array($dmgEffect,$debuffEffect);
					$action = new SkillAction($actionEffects,TARGET::UNIQUE,TARGET::ENNEMY);
					$skillActions = array($action);
					$skillCooldown = 1;
					$skillActive = true;
					break;
				case "surpriseBox" :
					$skillName = "surprise box";
					$dmgFormula = array(array(240),array("%"),array("ATK"));
					$dmgEffect = SkillLib::get_damage_effect($dmgFormula);
					$debuffData=array("type"=>DEBUFF::STUN,"turns"=>1,"perCent"=>100);
					$debuffStunEffect = SkillLib::get_single_effect(EFFECT::debuff,$debuffData);
					$debuffData=array("type"=>DEBUFF::GLANCING,"turns"=>1,"perCent"=>100);
					$debuffGlancingEffect = SkillLib::get_single_effect(EFFECT::debuff,$debuffData);
					$debuffData=array("type"=>DEBUFF::LOWSPD,"turns"=>1,"perCent"=>100);
					$debuffSpdEffect = SkillLib::get_single_effect(EFFECT::debuff,$debuffData);
					$actionEffects = array($dmgEffect,$debuffStunEffect,$debuffGlancingEffect,$debuffSpdEffect);
					$action = new SkillAction($actionEffects,TARGET::ALL,TARGET::ENNEMY);
					$action->apply_random();
					$skillActions = array($action);
					$skillCooldown = 4;
					$skillActive = true;
					break;
				case "amputationOfMagic" :
					$skillName = "Amputation of magic";
					$dmgFormula = array(array(68),array("%"),array("ATK"));
					$dmgEffect = SkillLib::get_damage_effect($dmgFormula,3,true);
					$actionEffects = array($dmgEffect);
					$action = new SkillAction($actionEffects,TARGET::ALL,TARGET::ENNEMY);
					$skillActions = array($action);
					$skillCooldown = 4;
					$skillActive = true;
					break;
				
				// Bernard
				case "Snatch" :
					$skillName = "Snatch";
					$dmgFormula = array(array("SPD","+",90),array("/"),array(0.55),array("%"),array("ATK"));
					$dmgEffect = SkillLib::get_damage_effect($dmgFormula);
					$actionEffects = array($dmgEffect);
					$action = new SkillAction($actionEffects,TARGET::UNIQUE,TARGET::ENNEMY);
					$skillActions = array($action);
					$skillCooldown = 1;
					$skillActive = true;
					break;
				case "BodySlam" :
					$skillName = "Body Slam";
					$dmgFormula = array(array(510),array("%"),array("ATK"));
					$dmgEffect = SkillLib::get_damage_effect($dmgFormula);
					$debuffData=array("type"=>DEBUFF::LOWATK,"turns"=>2,"perCent"=>100);
					$debuffAtkEffect = SkillLib::get_single_effect(EFFECT::debuff,$debuffData);
					$debuffData=array("type"=>DEBUFF::LOWDEF,"turns"=>2,"perCent"=>100);
					$debuffDefEffect = SkillLib::get_single_effect(EFFECT::debuff,$debuffData);
					$actionEffects = array($dmgEffect,$debuffAtkEffect,$debuffDefEffect);
					$action = new SkillAction($actionEffects,TARGET::UNIQUE,TARGET::ENNEMY);
					$skillActions = array($action);
					$skillCooldown = 3;
					$skillActive = true;
					break;
				case "TailWind" :
					$skillName = "Tail Wind";
					$buffData=array("type"=>BUFF::RAISESPD,"turns"=>2,"perCent"=>100);
					$buffSpdEffect = SkillLib::get_single_effect(EFFECT::buff,$buffData);
					$raiseAtkBarEffect = SkillLib::get_single_effect(EFFECT::raiseAtkBar,30);
					$actionEffects = array($buffSpdEffect,$raiseAtkBarEffect);
					$action = new SkillAction($actionEffects,TARGET::ALL,TARGET::ALLIES);
					$skillActions = array($action);
					$skillCooldown = 5;
					$skillActive = true;
					break;
					
				// Baretta
				case "SpiritThrow" :
					$skillName = "Spirit Throw";
					$dmgFormula = array(array(380),array("%"),array("ATK"),array("-"),array(20));
					$dmgEffect = SkillLib::get_damage_effect($dmgFormula);
					$actionEffects = array($dmgEffect);
					$actionDmg = new SkillAction($actionEffects,TARGET::UNIQUE,TARGET::ENNEMY);
					$buffData=array("type"=>BUFF::RAISECR,"turns"=>1,"perCent"=>100);
					$buffCREffect = SkillLib::get_single_effect(EFFECT::buff,$buffData);
					$actionEffects = array($buffCREffect);
					$actionBuff = new SkillAction($actionEffects,TARGET::SELF,TARGET::ALLIES);
					$skillActions = array($actionDmg,$actionBuff);
					$skillCooldown = 1;
					$skillActive = true;
					break;
				case "Turbulence" :
					$skillName = "Turbulence";
					$dmgFormula = array(array(600),array("%"),array("ATK"));
					$dmgEffect = SkillLib::get_damage_effect($dmgFormula);
					$lowATBEffect = SkillLib::get_single_effect(EFFECT::lowAtkBar,100);
					$actionEffects = array($dmgEffect,$lowATBEffect);
					$action = new SkillAction($actionEffects,TARGET::UNIQUE,TARGET::ENNEMY);
					$skillActions = array($action);
					$skillCooldown = 4;
					$skillActive = true;
					break;
				case "PhoenixFury" :
					$skillName = "Phoenix's Fury";
					$dmgFormula = array(array(395),array("%"),array("ATK"));
					$dmgEffect = SkillLib::get_damage_effect($dmgFormula);
					$actionEffects = array($dmgEffect);
					$debuffData = array("type"=>DEBUFF::STUN,"turns"=>1,"perCent"=>50);
					$debuffEffect = SkillLib::get_single_effect(EFFECT::debuff,$debuffData);
					$actionEffects = array($dmgEffect,$debuffEffect,$debuffEffect);
					$action = new SkillAction($actionEffects,TARGET::ALL,TARGET::ENNEMY);
					$skillActions = array($action);
					$skillCooldown = 1;
					$skillActive = true;
					break;
					
				// Mav
				case "HonorAttack" :
					$skillName = "Honorable Attack";
					$dmgFormula = array(array(380),array("%"),array("ATK"),array("-"),array(20));
					$dmgEffect = SkillLib::get_damage_effect($dmgFormula);
					$debuffData=array("type"=>DEBUFF::STUN,"turns"=>1,"perCent"=>100);
					$stunEffect = SkillLib::get_single_effect(EFFECT::debuff,$debuffData);
					$actionEffects = array($dmgEffect,$stunEffect);
					$action = new SkillAction($actionEffects,TARGET::UNIQUE,TARGET::ENNEMY);
					$skillActions = array($action);
					$skillCooldown = 1;
					$skillActive = true;
					break;
				case "DeclareWar" :
					$skillName = "Declare War";
					$healEffect = SkillLib::get_single_effect(EFFECT::heal,25);
					$actionEffects = array($healEffect);
					$actionHeal = new SkillAction($actionEffects,TARGET::SELF,TARGET::ALLIES);
					$debuffData = array("type"=>DEBUFF::PROVOKE,"turns"=>1,"perCent"=>100);
					$debuffEffect = SkillLib::get_single_effect(EFFECT::debuff,$debuffData);
					$actionEffects = array($debuffEffect);
					$actionDebuff = new SkillAction($actionEffects,TARGET::UNIQUE,TARGET::ENNEMY);
					$skillActions = array($actionHeal,$actionDebuff);
					$skillCooldown = 3;
					$skillActive = true;
					break;
				case "WingsOfWind" :
					$skillName = "Wings of Wind";
					$buffData=array("type"=>BUFF::RAISESPD,"turns"=>2,"perCent"=>100);
					$buffSpdEffect = SkillLib::get_single_effect(EFFECT::buff,$buffData);
					$lowCooldownEffect = SkillLib::get_single_effect(EFFECT::lowcooldown,1);
					$cleanseEffect = SkillLib::get_single_effect(EFFECT::cleanse,1);
					$actionEffects = array($cleanseEffect,$lowCooldownEffect,$buffSpdEffect);
					$action = new SkillAction($actionEffects,TARGET::ALL,TARGET::ALLIES);
					$skillActions = array($action);
					$skillCooldown = 3;
					$skillActive = true;
					break;
			}
			return new Skill($skillName,$skillActions,$skillCooldown,$skillActive);
		}
		
		
		private function get_damage_effect($dmgFormula,$nbHits=1,$ignoreDef=false){
			return new Damage($dmgFormula,$nbHits,$ignoreDef);
		}
		
		private function get_single_effect($effectType,$data){
			return new SingleEffect($effectType,$data);
		}
		
	}
	
	
?>