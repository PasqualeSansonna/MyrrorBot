<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>FoodRecSys</title>
        <meta name="description" content="Core HTML Project">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        
        <!-- External CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Lato:300,400|Work+Sans:300,400,700" rel="stylesheet">

        
        <!-- CSS -->
        <link rel="stylesheet" href="css/style.min.css">
        <link rel="stylesheet" href="http://netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css">
                
    </head>
    <body data-spy="scroll" data-target="#navbar-nav-header" class="single-layout">
        
        <?php
        include "php/requestFunctions.php";
        
        $postPage = 'action="recipes.php"';
        $answers = '';
                
        //Se nel Post e' presente la variabile dish, vuol dire che non e' stato effettuato l'accesso con myrror, oppure le ricette sono state gia' salvate in $data (quindi stiamo visualizzando un secondo o un dolce)
        if(isset($_POST['dish'])){
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
                
                $data = getRecipes(createURL($mood, $stress, $depression, $underweight, $overweight, $activity, $sleep, $vegetarian, $lactose, $gluten, $nickel, $light, $exp));
                
                $answers = $answers . $data['personalized_main']['url'] . ','. $data['not_personalized_main']['url'] . ',' . $data['personalized_second']['url'] . ',' . $data['not_personalized_second']['url'] . ',' .$data['personalized_dessert']['url'] . ',' . $data['not_personalized_dessert']['url'] . ',';
                
            }
            else{
                $data_var = $_POST["data"];
                $data = unserialize(base64_decode($data_var));
            }

            if($dish == "dessert"){
                $postPage = 'action="bye.php"';
            }
        }
        //se non si trova la variabile dish vuol dire che abbiamo effettuato l'accesso con myrror, quindi dobbiamo caricare le ricette personalizzate
        else{
            $dish = $_COOKIE['myrrorDish'];
            $myrrorData = unserialize(base64_decode($_POST["myrrorData"]));     

            
            $sex = $_POST['sexOption'];
            
            $answers = $sex . ":q,";
            
            $occupation = $_POST['occupation'];
            $websiteUsage = $_POST['websiteUsage'];
            $cookingFreq = $_POST['cookingFreq'];
            $exp = $_POST['exp'];
            $goal = $_POST['goal'];
            
            if(isset($_POST['age'])){
                $age = $_POST['age'];
                $answers = $answers . $age . ":q,";
            }
            else{
                $age = $myrrorData['age'];
                $answers = $answers . $age . ":m,";
            }
            
            if(isset($_POST['BMI'])){
                $fatclass = $_POST['BMI'];
                
                $answers = $answers . $fatclass . ":q,";
                
                $underweight = false;
                $overweight = false;
                
                if($fatclass == 'Underweight (BMI < 19.0)')
                    $underweight = true;
                
                if($fatclass == 'Overweight (BMI 23.0–24.9)' || $fatclass == 'Class I obesity (BMI 25.0–29.9)' || $fatclass == 'Class II obesity (BMI ≥30.0)' )
                    $overweight = true;
            }
            else{
                $underweight = $myrrorData['underweight'];
                $overweight = $myrrorData['overweight'];
                
                if($underweight)
                   $fatclass = 'underweight';
                   
                if($overweight)
                   $fatclass = 'overweight';
                
                $answers = $answers . $fatclass . ":m,";
            }
            
            $answers = $answers . $occupation . ":q," . $websiteUsage . ":q," . $cookingFreq . ":q," . $exp . ":q," . $goal . ":q,"; 
            
            if(isset($_POST['mood'])){
                $mood = $_POST['mood'];
                
                $answers = $answers . $mood . ":q,";
            }
            else{
                $mood = $myrrorData['mood'];
                
                $answers = $answers . $mood . ":m,";
            }
            
            if(isset($_POST['activity'])){
                $activity = $_POST['activity'];
                
                 $answers = $answers . $activity . ":q,";
            }
            else{
                $activity = $myrrorData['activity'];
                
                $answers = $answers . $activity . ":m,";
            }
            
            if(isset($_POST['sleepOption'])){
                $sleep = $_POST['sleepOption'];
                
                $answers = $answers . $sleep . ":q,";
            }
            else{
                $sleep = $myrrorData['sleep'];
                
                $answers = $answers . $sleep . ":m,";
            }
            
            if(isset($_POST['stressOption'])){
                $stress = $_POST['stressOption']; 
                
                $answers = $answers . $stress . ":q,";
            }
            else{
                $stress = $myrrorData['stress'];
                
                $answers = $answers . $stress . ":m,";
            }   
            
            if(isset($_POST['depressionOption'])){
                $depression = $_POST['depressionOption'];
                
                $answers = $answers . $depression . ":q,";
            }
            else{
                $depression = $myrrorData['depression'];
                
                $answers = $answers . $depression . ":m,";
            }

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
            
            
            $data = getRecipes(createURL($mood, $stress, $depression, $underweight, $overweight, $activity, $sleep, $vegetarian, $lactose, $gluten, $nickel, $light, $exp));  
            
            $answers = $answers . $data['personalized_main']['url'] . ',' . $data['not_personalized_main']['url'] . ',' . $data['personalized_second']['url'] . ',' . $data['not_personalized_second']['url'] . ',' . $data['personalized_dessert']['url'] . ',' . $data['not_personalized_dessert']['url'] . ',';
        }
        
        $dish_name = $dish;
        if($dish_name !== 'dessert')
            $dish_name = $dish_name . ' course';
        
        //salvo le risposte al questionario
        
        if(isset($_POST['answers']))
            $answers = $_POST['answers'];        
        
        if(isset($_POST['Q1']))
            $answers = $answers . $_POST['Q1'] . ',';
        
        if(isset($_POST['Q2']))
            $answers = $answers . $_POST['Q2'] . ',';
         else if($dish !== "main")
             $answers = $answers . ',';

        if(isset($_POST['Q3']))
            $answers = $answers . $_POST['Q3'] . ',';
         else if($dish !== "main")
             $answers = $answers . ',';

        if(isset($_POST['Q4']))
            $answers = $answers . $_POST['Q4'] . ',';
         else if($dish !== "main")
             $answers = $answers . ',';

        if(isset($_POST['Q5']))
            $answers = $answers . $_POST['Q5'] . ',';
         else if($dish !== "main")
             $answers = $answers . ',';
                                         
        if(isset($_POST['Q6']))
            $answers = $answers . $_POST['Q6'] . ',';
         else if($dish !== "main")
             $answers = $answers . ',';

        if(isset($_POST['Q7']))
            $answers = $answers . str_replace(",", " ", $_POST['Q7']) . ',';
        else if($dish !== "main")
             $answers = $answers . ',';
                
        ?>
        
        <div class="boxed-page">
            <nav id="gtco-header-navbar" class="navbar">
                <div class="container" >
                    <span class="navbar-brand">  <!-- mx-auto -->
                        <img src="icons/icon.png" width="80" height="62" class="d-inline-block align-top" alt="">
                        <a href="index.html">Holistic User Models for Food Recommendation</a>
                    </span>
                </div>
            </nav>
            <section id="gtco-single-content" class="bg-white">
                <div class="container">
                    <div class="section-content blog-content">
                        
                         <!-- Section Title -->
                        <div class="title-wrap">
                            <h2 class="section-title">Your recipes</h2>
                            <p class="section-sub-title">Take a look to the <?php echo $dish_name;?> and answer the questions</p>
                        </div>
                        <!-- End of Section Title -->
                                                
                        <div class="row">  <!-- none, show-->
                            <?php
                                $pRecipeName = $data[('personalized_'.$dish)]['name'];
                                $pImgURL = $data[('personalized_'.$dish)]['imgURL'];
                                $pIngredients = $data[('personalized_'.$dish)]['ingredients'];
                                $pDescription = $data[('personalized_'.$dish)]['description'];
                                $pURL = $data[('personalized_'.$dish)]['url'];
                                $pIngredients = createIngText($pIngredients);
                                
                                
                                $recipeName = $data[('not_personalized_'.$dish)]['name'];
                                $imgURL = $data[('not_personalized_'.$dish)]['imgURL'];
                                $ingredients = $data[('not_personalized_'.$dish)]['ingredients'];
                                $description = $data[('not_personalized_'.$dish)]['description'];
                                $URL = $data[('not_personalized_'.$dish)]['url'];
                                $ingredients = createIngText($ingredients);

                                ?>
                             <!-- <a href="<?php echo $pURL;?>" target="_blank" class="col-md-6 blog-item-wrapper"> -->
			<div class="col-md-6 blog-item-wrapper">
                                <div class="blog-item h-100">
                                    <div class="blog-img">
                                        <img src="<?php echo $pImgURL;?>" alt="">
                                    </div>
                                    <div class="blog-text">
                                        <div class="blog-title text-center">
                                            <h4><?php echo $pRecipeName;?></h4>
                                        </div>
                                        <div class="blog-desc">
                                            <p><font color="black"><?php echo $pDescription;?></font></p>
                                        </div>
                                        <div class="blog-author">
                                            <p><?php echo $pIngredients;?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        
                        <!-- <a href="<?php echo $URL;?>" target="_blank" class="col-md-6 blog-item-wrapper"> -->
			 <div class="col-md-6 blog-item-wrapper">
                                <div class="blog-item h-100">
                                    <div class="blog-img">
                                        <img src="<?php echo $imgURL;?>" alt="">
                                    </div>
                                    <div class="blog-text">
                                        <div class="blog-title text-center">
                                            <h4><?php echo $recipeName;?></h4>
                                        </div>
                                        <div class="blog-desc">
                                            <p><font color="black"><?php echo $description;?></font></p>
                                        </div>
                                        <div class="blog-author">
                                            <p><?php echo $ingredients;?></p>
                                        </div>
                                    </div>
                                </div>
                            </div> 
                        </div>
                        
                        <div class='row'>
                            <?php
                        
                            $actual_dish = $dish;
                            if($dish == 'main')
                                $dish = 'second';
                            else if ($dish == 'second')
                                $dish = 'dessert';
                            
                            ?>
                                                        
                            <div class="col-md-11 offset-md-1 contact-form-holder mt-4">
                                
                                <form id="recipeForm" method="post" <?php echo $postPage; ?>>
                                    <input type="hidden" name="dish" id="hiddenField" value="<?php echo $dish;?>" />
                                    <input type="hidden" id="data" name="data" value="<?php print base64_encode(serialize($data))?>" />
                                    <input type="hidden" id="answers" name="answers" value="<?php echo $answers;?>" />
                                    
                                    <div class="form-group row">
                                        <label for="Q1" class="col-sm-6 col-form-label">Which recipe do you prefer?</label>
                                        <div class="col-sm-5">
                                            <select class="form-control" id="Q1" name="Q1" required onchange="dynamicForm()">
                                                <!--<option hidden disabled selected value></option>-->
                                                <option hidden selected></option>
                                                <option >Left side recipe</option>
                                                <option>None of these two</option>
                                                <option >Right side recipe</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group row" id="labelPreQuest">
                                        <label class="col-sm-12 col-form-label">Why did you choose this recipe? - Remember: <u><i>1 Star means completely disagree, 5 Stars mean completely agree</i></u></label>
                                    </div>
                                    
                                    <div class="form-group row" id="Q2div"> <!-- style="display: none;"-->
                                        <label for="Q2" class="col-sm-6 col-form-label">It seems savory and tastier</label>
                                        <fieldset class="rating">					 			    
                                            <input type="radio" id="star5Q2" name="Q2" value="5" required/>
                                            <label class = "full" for="star5Q2" title="5 - completely agree"></label>
                                            
                                            <input type="radio" id="star4Q2" name="Q2" value="4" required/>
                                            <label class = "full" for="star4Q2" title="4 - agree"></label>
                                            
                                            <input type="radio" id="star3Q2" name="Q2" value="3" required/>
                                            <label class = "full" for="star3Q2" title="3 - neither agree or disagree"></label>
                                            
                                            <input type="radio" id="star2Q2" name="Q2" value="2" required/>
                                            <label class = "full" for="star2Q2" title="2 - disagree"></label>
                                            
                                            <input type="radio" id="star1Q2" name="Q2" value="1" required/>
                                            <label class = "full" for="star1Q2" title="1 - completely disagree"></label>
                                        </fieldset>
                                    </div>
                                    
                                    <div class="form-group row" id="Q3div">    
                                        <label for="Q3" class="col-sm-6 col-form-label">It helps me to eat more healthily</label>
                                        <fieldset class="rating">					 			    
                                            <input type="radio" id="star5Q3" name="Q3" value="5" required/>
                                            <label class = "full" for="star5Q3" title="5 - completely agree"></label>
                                            
                                            <input type="radio" id="star4Q3" name="Q3" value="4" required/>
                                            <label class = "full" for="star4Q3" title="4 - agree"></label>
                                            
                                            <input type="radio" id="star3Q3" name="Q3" value="3" required/>
                                            <label class = "full" for="star3Q3" title="3 - neither agree or disagree"></label>
                                            
                                            <input type="radio" id="star2Q3" name="Q3" value="2" required/>
                                            <label class = "full" for="star2Q3" title="2 - disagree"></label>
                                            
                                            <input type="radio" id="star1Q3" name="Q3" value="1" required/>
                                            <label class = "full" for="star1Q3" title="1 - completely disagree"></label>
                                        </fieldset>
                                    </div>
                                    
                                    <div class="form-group row" id="Q4div">    
                                        <label for="Q4" class="col-sm-6 col-form-label">It would help me to lose/gain weight</label>
                                        <fieldset class="rating">					 			    
                                            <input type="radio" id="star5Q4" name="Q4" value="5" required/>
                                            <label class = "full" for="star5Q4" title="5 - completely agree"></label>
                                            
                                            <input type="radio" id="star4Q4" name="Q4" value="4" required/>
                                            <label class = "full" for="star4Q4" title="4 - agree"></label>
                                            
                                            <input type="radio" id="star3Q4" name="Q4" value="3" required/>
                                            <label class = "full" for="star3Q4" title="3 - neither agree or disagree"></label>
                                            
                                            <input type="radio" id="star2Q4" name="Q4" value="2" required/>
                                            <label class = "full" for="star2Q4" title="2 - disagree"></label>
                                            
                                            <input type="radio" id="star1Q4" name="Q4" value="1" required/>
                                            <label class = "full" for="star1Q4" title="1 - completely disagree"></label>
                                        </fieldset>
                                    </div>
                                    
                                    <div class="form-group row" id="Q5div">    
                                        <label for="Q5" class="col-sm-6 col-form-label">It seems easier to prepare</label>
                                        <fieldset class="rating">					 			    
                                            <input type="radio" id="star5Q5" name="Q5" value="5" required/>
                                            <label class = "full" for="star5Q5" title="5 - completely agree"></label>
                                            
                                            <input type="radio" id="star4Q5" name="Q5" value="4" required/>
                                            <label class = "full" for="star4Q5" title="4 - agree"></label>
                                            
                                            <input type="radio" id="star3Q5" name="Q5" value="3" required/>
                                            <label class = "full" for="star3Q5" title="3 - neither agree or disagree"></label>
                                            
                                            <input type="radio" id="star2Q5" name="Q5" value="2" required/>
                                            <label class = "full" for="star2Q5" title="2 - disagree"></label>
                                            
                                            <input type="radio" id="star1Q5" name="Q5" value="1" required/>
                                            <label class = "full" for="star1Q5" title="1 - completely disagree"></label>
                                        </fieldset>
                                    </div>
                                    
                                    <div class="form-group row " id="Q6div">
                                        <label for="Q6" class="col-sm-8 radio control-label">I chose it because there was no other choice. I would not have chosen any of these</label>
                                        <div class="col-sm-3">
                                            <div class="form-check form-check-inline">
                                              <input class="form-check-input" type="radio" name="Q6" id="y" value="yes" required>
                                              <label class="form-check-label" for="y">Yes</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                              <input class="form-check-input" type="radio" name="Q6" id="n" value="no" required>
                                              <label class="form-check-label" for="n">No</label>
                                            </div>
                                        </div>
                                   </div>
                                    
                                    <div class="form-group row" id="Q7div">    
                                        <label for="Q7" class="col-sm-6 col-form-label">Other: </label>
                                        <div class="col-sm-5">
                                            <textarea class="form-control" id="Q7" name="Q7" rows="2"></textarea>
                                        </div>
                                    </div>
                                    
                                    
                                
                                    <div class="col-md-10 form-btn text-center">
                                        <button id="btnForm" class="btn btn-block btn-secondary btn-red col-md-4 offset-md-4 " type="submit" name="submit" >Continue</button>
                                    </div>
                                </form>
                            </div>
                        
                        </div>
                    </div>
                </div>
            </section>
            <footer class="mastfoot mb-3 bg-white py-4 border-top">
                <div class="inner container">
                    <div class="row">
                        <div class="col-md-6 d-flex">
                            <span> &copy; 2019 <a href="http://www.di.uniba.it/~swap/" target="_blank"> SWAP Research Group </a>,</span>
                            <span>&nbsp;<a href="privacy.html" target="_blank">Privacy Policy </a></span>
                        </div>
                        <div class="col-md-6 d-flex flex-row-reverse  ">
                            <span>Developed by <a href="https://github.com/itkkk" target="_blank">Antonio Pellicani</a> &amp; <a href="https://github.com/astarrr" target="_blank">Angelo Sparapano</a></span>
                        </div>  
                    </div>
                </div>
            </footer>
        </div>
        
        <!-- External JS -->
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
        
        <!-- Main JS -->
        <script>
            function dynamicForm(){
                var e = document.getElementById("Q1");
                var value = e.options[e.selectedIndex].text;
                if(value == "None of these two"){
                   document.getElementById( 'Q2div' ).style.display = 'none';
                   document.getElementById( 'Q3div' ).style.display = 'none';
                   document.getElementById( 'Q4div' ).style.display = 'none';
                   document.getElementById( 'Q5div' ).style.display = 'none';
                   document.getElementById( 'Q6div' ).style.display = 'none';
                   document.getElementById( 'Q7div' ).style.display = 'none';
                   document.getElementById( 'labelPreQuest' ).style.display = 'none';
                   
                   document.getElementById( 'star5Q2' ).required = false;
                   document.getElementById( 'star4Q2' ).required = false;
                   document.getElementById( 'star3Q2' ).required = false;
                   document.getElementById( 'star2Q2' ).required = false;
                   document.getElementById( 'star1Q2' ).required = false;
                    
                   document.getElementById( 'star5Q3' ).required = false;
                   document.getElementById( 'star4Q3' ).required = false;
                   document.getElementById( 'star3Q3' ).required = false;
                   document.getElementById( 'star2Q3' ).required = false;
                   document.getElementById( 'star1Q3' ).required = false;
                    
                   document.getElementById( 'star5Q4' ).required = false;
                   document.getElementById( 'star4Q4' ).required = false;
                   document.getElementById( 'star3Q4' ).required = false;
                   document.getElementById( 'star2Q4' ).required = false;
                   document.getElementById( 'star1Q4' ).required = false;
                
                   document.getElementById( 'star5Q5' ).required = false;
                   document.getElementById( 'star4Q5' ).required = false;
                   document.getElementById( 'star3Q5' ).required = false;
                   document.getElementById( 'star2Q5' ).required = false;
                   document.getElementById( 'star1Q5' ).required = false;
                    
                   document.getElementById( 'y' ).required = false;
                   document.getElementById( 'n' ).required = false;
                   }
                else{
                   document.getElementById( 'Q2div' ).style.display = '';
                   document.getElementById( 'Q3div' ).style.display = '';
                   document.getElementById( 'Q4div' ).style.display = '';
                   document.getElementById( 'Q5div' ).style.display = '';
                   document.getElementById( 'Q6div' ).style.display = '';
                   document.getElementById( 'Q7div' ).style.display = '';
                   document.getElementById( 'labelPreQuest' ).style.display = '';
                
                   document.getElementById( 'star5Q2' ).required = true;
                   document.getElementById( 'star4Q2' ).required = true;
                   document.getElementById( 'star3Q2' ).required = true;
                   document.getElementById( 'star2Q2' ).required = true;
                   document.getElementById( 'star1Q2' ).required = true;
                    
                   document.getElementById( 'star5Q3' ).required = true;
                   document.getElementById( 'star4Q3' ).required = true;
                   document.getElementById( 'star3Q3' ).required = true;
                   document.getElementById( 'star2Q3' ).required = true;
                   document.getElementById( 'star1Q3' ).required = true;
                    
                   document.getElementById( 'star5Q4' ).required = true;
                   document.getElementById( 'star4Q4' ).required = true;
                   document.getElementById( 'star3Q4' ).required = true;
                   document.getElementById( 'star2Q4' ).required = true;
                   document.getElementById( 'star1Q4' ).required = true;
                
                   document.getElementById( 'star5Q5' ).required = true;
                   document.getElementById( 'star4Q5' ).required = true;
                   document.getElementById( 'star3Q5' ).required = true;
                   document.getElementById( 'star2Q5' ).required = true;
                   document.getElementById( 'star1Q5' ).required = true;
                    
                   document.getElementById( 'y' ).required = true;
                   document.getElementById( 'n' ).required = true;    

                   }
            }
        </script>
        <!-- <script src="js/app.min.js "></script> -->
        <!-- <script src="//localhost:35729/livereload.js"></script> -->
    </body>
</html>