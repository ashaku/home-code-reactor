<?
	// apply a genetic algorithm to select the better runes for a monster
	// genetic codes represent a set of runes to apply on monsters
	// Evaluation make monsters fight 1v1, the winner get 1 point, the looser get -1 point
	// Each member of population have a fight with a random opponent, so everyone have at least 1 mandatory fight, but statically have around 2 fights.
	// Selection take the upper half better scores and reproduce them
	// Reproduction is made with 3 cross points (random position each time) :
	// cross points  :        .       .  .
	// parent 1 code : AAAAAAAAAAAAAAAAAAAAAAAA
	// parent 2 code : BBBBBBBBBBBBBBBBBBBBBBBB
	// child code    : AAAAAAABBBBBBBBAAABBBBBB
	// Child code is then mutated once, this will introduce new possibilities of genetic codes. Otherwise, population only have its starting gene pool and don't evolve to new solutions
	// Selected parents and their children form a new generation and the cycle start over
	
	include("Monster.php");
	
	
	class AlgoGenSWSA{
		
		private $verbose;
		private $size_population;
		private $nb_generations;
		private $population;
		private $scores;			// Array [id_in_population => score]
		private $tmp_pop;
		
		function __construct($aSize,$aLength,$aVerbose=false){
			if ( $this->verbose ) echo "<br>Create AlgoGen for $aSize individuals over $aLength generations";
			$this->verbose = $aVerbose;
			$this->size_population = $aSize;
			$this->nb_generations = $aLength;
			$this->population = array();
			$this->run();
		}
		
		// Start generating
		public function run(){
			//if ( $this->verbose ) echo "";
			$this->genese();
			$this->display();
			for ( $g=1; $g<=$this->nb_generations; $g++ ){
				$this->evaluation();
				$this->selection();
				$this->reproduction();
				$this->display();
			}
			$best = MonsterLib::load_monster("Veromos",$this->population[0]);
			$best->display();
		}
		
		// Create the starting population with random codes
		private function genese(){
			for ( $i=0; $i<$this->size_population; $i++ ){
				$code = "";
				for ( $j=0; $j<93; $j++ )	$code .= mt_rand(0,1);
				$this->population[$i] = $code;
				$this->scores[$i] = 0;
				if ( $this->verbose ) echo "<br>[GENESE] ".$code;
			}
		}
		
		// Give a rank for each individual in population
		private function evaluation(){
			
			// Clean scores array
			$this->scores = array();
			for ( $i=0; $i<$this->size_population; $i++ ) $this->scores[$i] = 0;
			
			// For each individuals in population
			foreach ( $this->population as $id => $code ){
			
				// Pick another individual for opponent
				do{	$id_opponent = mt_rand(0,$this->size_population-1);	}while($id==$id_opponent);
				
				// Fight !
				$code_opponent = $this->population[$id_opponent];
				$mon1 = MonsterLib::load_monster("Veromos",$code);
				$mon2 = MonsterLib::load_monster("Veromos",$code_opponent);
				$team1 = array($mon1);
				$team2 = array($mon2);
				$combat = new Combat($team1,$team2);
				$winner = $combat->get_winner();
				if ( $this->verbose ) echo "<br>[EVAL] ".$id." pick ".$id_opponent." and ";
				if ( $winner == 1 ){
					// current individual is winner
					$this->scores[$id] += 1;
					$this->scores[$id_opponent] -= 1;
					if ( $this->verbose ) echo "win";
				}else{
					// opponent is winner
					$this->scores[$id] -= 1;
					$this->scores[$id_opponent] += 1;
					if ( $this->verbose ) echo "loose";
				}
				if ( $this->verbose ) echo ". score:".$this->scores[$id]." opponent:".$this->scores[$id_opponent];
			}
		}
		
		// Copy best half of individuals in tmp_pop
		private function selection(){
			
			// Sort population by scores
			arsort ( $this->scores );
			if ( $this->verbose ){ echo "<br>[SELECT] scores : "; print_r($this->scores); }
			
			// Copy top half (best scores) into tmp_pop
			$this->tmp_pop = array();
			$mid = $this->size_population/2 - 1;
			$cpt = 0;
			foreach ( $this->scores as $id => $score ){
				array_push($this->tmp_pop,$this->population[$id]);
				if ( $cpt >= $mid ) return;
				$cpt++;
			}
		}
		
		// Reproduce individuals in tmp_pop. Each selected parent make 1 child, so the population doubles and get back to its original size
		private function reproduction(){
			
			//if ( $this->verbose ) echo "<br>[REPRODUCTION] tmp_pop : "; print_r($this->tmp_pop);
			
			// For each parent selected
			$p = count($this->tmp_pop)-1;
			for ( $id=0; $id<=$p; $id++ ){
				$code = $this->tmp_pop[$id];
				
				// Choose a partner among other selected
				do {	$id_partner = mt_rand(0,$p);	} while( $id_partner == $id );
				//if ( $this->verbose ) echo "<br>[REPRODUCTION] ".$id." pick ".$id_partner;
				
				// Cross parents codes
				$code_partner = $this->tmp_pop[$id_partner];
				$child = $this->cross_code ( $code, $code_partner );
				
				// Mutate child code
				$this->mutation ( $child );
				
				// Add child to tmp_pop 
				array_push($this->tmp_pop,$child);
				if ( $this->verbose ) echo "<br><br>&nbsp;".$code."<br>+".$code_partner."<br>=".$child;
			}
			
			// Save as new population
			$this->population = $this->tmp_pop;			
		}
		// Children codes are created by crossing parent's code
		private function cross_code ( $code1, $code2 ){
			
			// Define crossPoints
			$step = round(strlen($code1)/3);
			$crossPoints[0] = mt_rand ( 1, $step );
			$crossPoints[1] = mt_rand ( $step, $step*2 );
			$crossPoints[2] = mt_rand ( $step*2, strlen($code1)-1 );
			
			// For each gene (bit)
			$child = "";
			$j = 0; // Arbitrary choice of choosing parent1's code at first
			for ( $i=0; $i<strlen($code1); $i++ ){
				
				// Switch parent code to copy at each crossPoint
				foreach ($crossPoints as $cp ){
					if ( $i == $cp ) $j++;
				}
				
				if ( $j % 2 == 0 ){
					// Copy parent 1 genes
					$child .= $code1[$i];
				}else{
					// Copy parent 2 genes
					$child .= $code2[$i];
				}
			}
			return $child;
		}
		// Change 1 random bit of child code, set it with a random value (can be the same as original)
		private function mutation(&$code){
			$code[mt_rand(0,strlen($code))] = mt_rand(0,1);
		}
		
		public function display(){
			$char = '|';
			foreach ( $this->population as $id => $code ){
				$r = dechex(bindec(substr($code,0,8)));
				if ( strlen($r) == 1 ) $r = "0".$r;
				$g = dechex(bindec(substr($code,8,1))*12);
				if ( strlen($g) == 1 ) $g = "0".$g;
				$b = dechex(bindec(substr($code,13,8)));
				if ( strlen($b) == 1 ) $b = "0".$b;
				echo "<span style='color:#".$r.$g.$b."'>".$char."</span>";
			}
			echo "<br>";
		}
		
	}
?>