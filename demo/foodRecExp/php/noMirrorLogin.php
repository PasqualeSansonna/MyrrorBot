<?php
	
	$dish = $_POST['dish'];       

	if($dish == "main"){
		$sex = $_POST['sexOption'];
		$age = $_POST['age']; 
		$fatclass = $_POST['BMI'];
		$occupation = $_POST['occupation'];
		$websiteUsage = $_POST['websiteUsage'];
		$cookingFreq = $_POST['cookingFreq'];
		$exp = $_POST['exp'];
		$goal = $_POST['goal'];
		$mood = $_POST['mood'];
		$activity = $_POST['activity']; 
		$sleep = $_POST['sleepOption'];
		$stress = $_POST['stressOption']; 
		$depression = $_POST['depressionOption']; 
		
		$joint 			= (isset($_POST['joint'])) 			? $_POST['joint'] 			: false;
		$cholesterol 	= (isset($_POST['cholesterol'])) 	? $_POST['cholesterol'] 	: false;
		$heart 			= (isset($_POST['heart'])) 			? $_POST['heart'] 			: false;
		$pressure 		= (isset($_POST['pressure'])) 		? $_POST['pressure'] 		: false;
		$diabete 		= (isset($_POST['diabete'])) 		? $_POST['diabete'] 		: false;

		$answers = $sex . ":q," . $age . ":q," . $fatclass . ":q," . $occupation . ":q," . $websiteUsage . ":q," . $cookingFreq . ":q," . $exp . ":q," . $goal . ":q," . $mood . ":q," . $activity . ":q," . $sleep . ":q," . $stress . ":q," . $depression . ":q,";
	   
		if(isset($_POST['vegetarian'])){
			$vegetarian = $_POST['vegetarian']; 
			$answers = $answers . "-vegetarian-";
		}
		else{
			$vegetarian = false;
		}

		if(isset($_POST['lactose'])){
			$lactose = $_POST['lactose'];
			$answers = $answers . "-lactosefree-";
		}
		else{
			$lactose = false;
		}

		if(isset($_POST['gluten'])){
			$gluten = $_POST['gluten'];
			$answers = $answers . "-glutenfree-";
		}
		else{
			$gluten = false;
		}

		if(isset($_POST['nickel'])){
			$nickel = $_POST['nickel'];
			$answers = $answers . "-lownickel-";
		}
		else{
			$nickel = false;
		}

		if(isset($_POST['light'])){
			$light = $_POST['light']; 
			$answers = $answers . "-light-";
		}
		else{
			$light = false;
		}
		
		$answers = $answers . ',';
		
		$underweight = false;
		$overweight = false;
		if($fatclass == 'Underweight (BMI < 19.0)')
			$underweight = true;
		if($fatclass == 'Overweight (BMI 23.0–24.9)' || $fatclass == 'Class I obesity (BMI 25.0–29.9)' || $fatclass == 'Class II obesity (BMI ≥30.0)' )
			$overweight = true;
		
		
		//here is created the recommendation
		$data = getRecipes(createURL($mood, $stress, $depression, $underweight, $overweight, $activity, $sleep, $vegetarian, $lactose, $gluten, $nickel, $light, $exp));
		
		$explanations = [];
		
		$imgurlA = $data['personalized_main']['imgURL'];
		$imgurlB = $data['not_personalized_main']['imgURL'];
		
		$explanations["main_exp"] = getExplanation(createUrlExp(
			$mood, $stress, $depression,
			$underweight, $overweight, $activity, $goal, $sleep,
			$vegetarian, $lactose, $gluten, $nickel, $light,
			$joint, $cholesterol, $heart, $pressure, $diabete,
			$exp, $imgurlA, $imgurlB)
		);
		
		$imgurlA = $data['personalized_second']['imgURL'];
		$imgurlB = $data['not_personalized_second']['imgURL'];
		
		$explanations["second_exp"] = getExplanation(createUrlExp(
			$mood, $stress, $depression,
			$underweight, $overweight, $activity, $goal, $sleep,
			$vegetarian, $lactose, $gluten, $nickel, $light,
			$joint, $cholesterol, $heart, $pressure, $diabete,
			$exp, $imgurlA, $imgurlB)
		);
		
		$imgurlA = $data['personalized_dessert']['imgURL'];
		$imgurlB = $data['not_personalized_dessert']['imgURL'];
			
		$explanations["dessert_exp"] = getExplanation(createUrlExp(
			$mood, $stress, $depression,
			$underweight, $overweight, $activity, $goal, $sleep,
			$vegetarian, $lactose, $gluten, $nickel, $light,
			$joint, $cholesterol, $heart, $pressure, $diabete,
			$exp, $imgurlA, $imgurlB)
		);

		$answers = $answers 
			. $data['personalized_main']['name'] 
			. ','. $data['not_personalized_main']['name'] 
			. ',' . $data['personalized_second']['name'] 
			. ',' . $data['not_personalized_second']['name'] 
			. ',' .$data['personalized_dessert']['name'] 
			. ',' . $data['not_personalized_dessert']['name'] . ',';
	}
	else{
		$data_var = $_POST["data"];
		$data = unserialize(base64_decode($data_var));
	}

	if($dish == "dessert_exp"){
		$postPage = 'action="bye.php"';
	}

?>