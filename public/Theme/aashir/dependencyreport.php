<!DOCTYPE html>
<html>
  <head>
     <title>Aashir Engineering | Dependency Report</title>
    <?php require("head.php"); ?>
  </head>
  <body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
      <?php 
        require("nav.php"); 
        require("sidebar.php");
      ?>
      
		<!--  *************************** -->
		<div class="content-wrapper">
			<section class="content">
	     		<div class="container-fluid">
					<div class="row">
						<div class="col-12">
							<div class="card card-primary">
								<div class="card-header">
									<h3 class="card-title">Dependency</h3>
								</div>
								
								<div class="card-body table-responsive p-0">
									<div class="accordion" id="accordionExample">
									  <?php 
									  	displayTasksByProject('1','Project 1 Title'); 
									  	displayTasksByProject('2','Project 2 Title'); 
									  	displayTasksByProject('3','Project 3 Title'); 
									  	displayTasksByProject('4','Project 4 Title'); 
									  	displayTasksByProject('5','Project 5 Title'); 
									  ?>
									  
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
      <!--  ************************** -->

      <?php require("footer.php"); ?>
    </div>
    <!-- ./wrapper -->
    <?php require("addjs.php") ?>

    <?php

    	function displayTasksByProject($num,$ProjectTitle){
    		?>
    		<div class="card">
			    <div class="card-header bg-light" id="heading<?=$num;?>">
			    	<div class="row">
			    		<div class="col-12">
			    			<h5 class="mb-0">
						        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse<?=$num;?>" aria-expanded="true" aria-controls="collapse<?=$num;?>">
						          <?= $ProjectTitle; ?>
						        </button>
						    </h5>
			    		</div>
			    		
			    	</div>
			      
			    </div>

			    <div id="collapse<?=$num;?>" class="collapse" aria-labelledby="heading<?=$num;?>" data-parent="#accordionExample">
			      <div class="card-body">
			        <table class="table table-hover table-striped " width="100%">
                        
                        <tr style="background-color: #f5da88;">
                            <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
                        </tr>
                        <tr><td><hr></td></tr>
                        <tr style="background-color: #f5da88;">
                            <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
                        </tr>
                        <tr><td><hr></td></tr>
                        <tr style="background-color: #f5da88;">
                            <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
                        </tr>
                        <tr><td><hr></td></tr>
                        <tr style="background-color: #f5da88;">
                            <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
                        </tr>
                        <tr><td><hr></td></tr>

                      </table>
			      </div>
			    </div>
			  </div>
    		<?php
    	}
    ?>
  </body>
</html>




 