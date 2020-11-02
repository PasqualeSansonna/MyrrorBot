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
                            <h2 class="section-title">Thank you!</h2>
                            <p class="section-sub-title">The experiment is ended, enjoy your meal! &#x1F355;</p>

				       <?php
        //setta l'expiration date del cookie con un ora nel passato, eliminandolo nel caso sia settato (login con myrror)
        setcookie('myrror', '', time() - 3600);
        setcookie('myrrorDish', '', time() - 3600);


        $answers = $_POST['answers'];
               if(isset($_POST['Q1']))
            $answers = $answers . $_POST['Q1'] . ',';
        
        if(isset($_POST['Q2']))
            $answers = $answers . $_POST['Q2'] . ',';
         else 
             $answers = $answers . ',';

        if(isset($_POST['Q3']))
            $answers = $answers . $_POST['Q3'] . ',';
        else 
             $answers = $answers . ',';

        if(isset($_POST['Q4']))
            $answers = $answers . $_POST['Q4'] . ',';
        else 
             $answers = $answers . ',';

        if(isset($_POST['Q5']))
            $answers = $answers . $_POST['Q5'] . ',';
         else 
             $answers = $answers . ',';
                                         
        if(isset($_POST['Q6']))
            $answers = $answers . $_POST['Q6'] . ',';
         else 
             $answers = $answers . ',';

        if(isset($_POST['Q7']))
            $answers = $answers . str_replace(",", " ", $_POST['Q7']);
        
	$randomNumber = rand(1000000,9999999);

	$answers = $answers . $randomNumber;
        
        $myfile = file_put_contents('results/results.csv', $answers.PHP_EOL , FILE_APPEND | LOCK_EX);
        

			    echo "This is the code to complete your experiment:<br />"; echo $randomNumber; ?>
                        </div>
                        <!-- End of Section Title -->
                        
                        <div class="col-md-12 offset-md-1 contact-form-holder mt-4">
                            <form id="recipeForm" action="index.html">
                                <div class="col-md-10 form-btn text-center">
                                    <button id="btnForm" class="btn btn-block btn-secondary btn-red col-md-4 offset-md-4 " type="submit" name="submit" >Exit</button>
                                </div>
                            </form>
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
        <!-- <script src="js/app.min.js "></script> -->
        <!-- <script src="//localhost:35729/livereload.js"></script> -->
    </body>
</html>