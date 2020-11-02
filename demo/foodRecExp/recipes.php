<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>

<!DOCTYPE html>
<html lang="en">
    <?php include "php/head.php"; ?>
    <body data-spy="scroll" data-target="#navbar-nav-header" class="single-layout">
        <?php print_r($_POST); echo("<hr />")?>
		
		
        <?php
        include "php/requestFunctions.php";
        
        $postPage = 'action="recipes.php"';
        $answers = '';
                
        //Se nel Post e' presente la variabile dish, vuol dire che non e' stato effettuato l'accesso con myrror, oppure le ricette sono state gia' salvate in $data (quindi stiamo visualizzando un secondo o un dolce)
        if(isset($_POST['dish'])){
            include "php/noMirrorLogin.php";
        }
        //se non si trova la variabile dish vuol dire che abbiamo effettuato l'accesso con myrror, quindi dobbiamo caricare le ricette personalizzate
        else{
			include "php/mirrorLogin.php";
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
			<?php include "php/header.php"; ?>
            <section id="gtco-single-content" class="bg-white">
                <div class="container">
                    <div class="section-content blog-content">
                        
                         <!-- Section Title -->
                        <div class="title-wrap">
                            <h2 class="section-title">Your recipes</h2>
                            <p class="section-sub-title">Take a look to the <?= $dish_name ?> and answer the questions</p>
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
							
							$exp = (strpos($dish, "exp")) ? true : false;

							?>
							<!-- <a href="<?= $pURL ?>" target="_blank" class="col-md-6 blog-item-wrapper"> -->
							<div class="col-lg-6 blog-item-wrapper recipe">
                                <div class="blog-item recipe-content">
                                    <div class="blog-img">
                                        <img src="<?= $pImgURL ?>" alt="">
                                    </div>
                                    <div class="blog-text">
                                        <div class="blog-title text-center">
                                            <h4><?= $pRecipeName ?></h4>
                                        </div>
                                        <div class="blog-desc">
                                            <p><font color="black"><?= $pDescription ?></font></p>
                                        </div>
                                        <div class="blog-author">
											<button data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
												Click to show ingredients
											</button>
											<div id="collapseOne" class="collapse"><p><?= $pIngredients ?></p></div>
                                        </div>
                                    </div>
                                </div>
								<?php if(!$exp) : ?>
									<div class="blog-item explanation">
										<div class="blog-text">
											<div class="blog-desc">
												<p><font color="black"><?= $explanations["main_exp"][0] ?></font></p>
											</div>
										</div>
									</div>
								<?php endif; ?>
                            </div>
							
							<!-- <a href="<?= $URL ?>" target="_blank" class="col-md-6 blog-item-wrapper"> -->
							<div class="col-lg-6 blog-item-wrapper recipe">
                                <div class="blog-item recipe-content">
                                    <div class="blog-img">
                                        <img src="<?= $imgURL ?>" alt="">
                                    </div>
                                    <div class="blog-text">
                                        <div class="blog-title text-center">
                                            <h4><?= $recipeName ?></h4>
                                        </div>
                                        <div class="blog-desc">
                                            <p><font color="black"><?= $description ?></font></p>
                                        </div>
                                        <div class="blog-author">
											<button data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseOne">
												Click to show ingredients
											</button>
											<div id="collapseTwo" class="collapse"><p><?= $ingredients ?></p></div>
                                        </div>
                                    </div>
                                </div>
								<?php if(!$exp) : ?>
									<div class="blog-item explanation">
										<div class="blog-text">
											<div class="blog-desc">
												<p><font color="black"><?= $explanations["main_exp"][1] ?></font></p>
											</div>
										</div>
									</div>
								<?php endif; ?>
                            </div>
                        </div>
                        
                        <div class='row'>
                            <?php
                        
                            //$actual_dish = $dish;
                            
							switch($dish){
								case "main":
									$dish = "main_exp";
									break;
								case "main_exp":
									$dish = "second";
									break;
								case "second":
									$dish = "second_exp";
									break;
								case "second_exp":
									$dish = "dessert";
									break;
								case "dessert":
									$dish = "dessert_exp";
									break;
							}
                            
                            ?>
                                                        
                            <div class="col-md-11 offset-md-1 contact-form-holder mt-4">
                                
                                <form id="recipeForm" method="post" <?= $postPage ?> >
                                    <input type="hidden" name="dish" id="hiddenField" value="<?= $dish ?>" />
                                    <input type="hidden" id="data" name="data" value="<?php print base64_encode(serialize($data))?>" />
                                    <input type="hidden" id="expl" name="expl" value="<?php print base64_encode(serialize($explanations))?>" />
                                    <input type="hidden" id="answers" name="answers" value="<?= $answers ?>" />
                                    
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
            <?php include "php/footer.php"; ?>
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