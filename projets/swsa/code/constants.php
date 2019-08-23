<?
	// Constant classes
	// Act like enum for clarity in code
	
	class ELMT{
		const water = 0;
		const fire = 1;
		const wind = 2;
		const dark = 3;
		const light = 4;
		
		public static function get_name($aElmtID){
			$tabElmtNames = array("water","fire","wind","dark","light");
			return $tabElmtNames[$aElmtID];
		}
	}
	
	class STATS{
		const HP = 0;
		const ATK = 1;
		const DEF = 2;
		const SPD = 3;
		const CR = 4;
		const CD = 5;
		const PRE = 6;
		const RES = 7;
		const MAXHP = 8;
		
		public static function get_long_name($aStatID){
			$tabStatsNames = array("HP","Attack","Defense","Speed","Critical Rate","Critical Damage","Precision","Resistance","Maximum HP");
			return $tabStatsNames[$aStatID];
		}
		public static function get_short_name($aStatID){
			$tabStatsShortNames = array("HP","ATK","DEF","SPD","CR","CD","PRE","RES","MAXHP");
			return $tabStatsShortNames[$aStatID];
		}
	}
	
	class RUNEFAMILY{
		// x 2
		const Energy = 0;
		const Guard = 1;
		const Blade = 2;
		const Focus = 3;
		const Endure = 4;
		const Nemesis = 5;
		const Will = 6;
		const Shield = 7;
		const Revenge = 8;
		const Destroy = 9;
		// x 4
		const Swift = 10;
		const Rage = 11;
		const Fatal = 12;
		const Despair = 13;
		const Vampire = 14;
		const Violent = 15;
		
		public static function get_name($aFamilyID){
			$tabFamilyNames = array("Energy","Guard","Blade","Focus","Endure","Nemesis","Will","Shield","Revenge","Destroy","Swift","Rage","Fatal","Despair","Vampire","Violent");
			return $tabFamilyNames[$aFamilyID];
		}
	}
	
	class EFFECT{
		const debuff = 0;			// add debuff on ennemy
		const buff = 1;				// add buff on ally
		const strip = 2;			// remove ennemy buff
		const cleanse = 3;			// remove ally debuff
		const heal = 4;				// raise ally hp
		const raiseAtkBar = 5;		// raise ally attack bar
		const lowAtkBar = 6;		// lower ennemy attack bar
		const lowcooldown = 7;		// lower ally skill cooldown
		const raisecooldown = 8;	// raise ennemy skill cooldown
		const revive = 9;			// recall ally from dead
		const lowdamage = 10;		// reduce damages
		const damage = 11;			// lower ennemy hp
		public static function get_name($aSkillTypeID){
			$tabSkillsNames = array("debuff","buff","strip","cleanse","heal","raise attack bar","lower attack bar","lower cooldown","raise cooldown","revive","low damage","damage");
			return $tabSkillsNames[$aSkillTypeID];
		}
	}
	
	class TARGET{
		const ENNEMY = 0;
		const ALLIES = 1;
		const UNIQUE = 10;
		const MULTI = 11;
		const ALL = 12;
		const SELF = 13;
		public static function get_name($aTargetID){
			$tabTargetsNames = array(0=>"Ennemy","ally",10=>"unique","multi","all","self");
			return $tabTargetsNames[$aTargetID];
		}
	}
	
	class DEBUFF{
		const STUN = 0;
		const LOWATK = 1;	// -50%
		const ASLEEP = 2;
		const BOMB = 3;
		const SILENCE = 4;
		const FREEZE = 5;
		const CONSTDMG = 6;	// -5%MAXHP
		const GLANCING = 7;
		const PROVOKE = 8;
		const MARK = 9;	// DMG+25%
		const LOWDEF = 10;	// -70%
		const LOWSPD = 11;	// -30%
		const NOHEAL = 12;
		
		public static function get_name($aDebuffID){
			$tabDebuffNames = array("Stun","Lower attack","Sleep","Bomb","Silence","Freeze","Constant Damages","Glancing","Provoke","Mark","Lower Defense","Lower Speed","Unrecoverable");
			return $tabDebuffNames[$aDebuffID];
		}
	}
	
	class BUFF{
		const RAISEATK = 0;	// +50%
		const RAISESPD = 1;	// +30%
		const RAISEDEF = 2;	// +70%
		const IMMUNITY = 3;
		const REFLECT = 4;	// 30%DMG
		const RAISECR = 5;	// +30%
		const HEAL = 6;		// +15%MAXHP
		const SHIELD = 7;
		const COUNTER = 8;	// 75%ATK
		const INVINCIBLE = 9;
		
		public static function get_name($aBuffID){
			$tabBuffNames = array("Raise ATK","Raise SPD","Raise DEF","Immunity","Reflect DMG","Raise CR","Heal","Shield","Counter","Invincible");
			return $tabBuffNames[$aBuffID];
		}
	}
?>