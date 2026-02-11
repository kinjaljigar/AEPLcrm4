<!DOCTYPE html>
<html>
  <head>
     <title>Aashir Engineering | Estimated Hours v/s Actual Hours</title>
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
									<h3 class="card-title">Estimated Hours v/s Actual Hours</h3>
								</div>
								
								<div class="card-body table-responsive p-0">
									<div class="accordion" id="accordionExample">
									  <?php 
									  	displayTasksByProject('1','Project 1 Title'); 
									  	
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
    		<table class="table table-hover table-striped table-bordered" >
                    <thead>
                        <tr>
                            <th width="33%">Project</th>
                            <th width="33%">Estimated Hours</th>
                            <th width="34%">Actual Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Reliance Industries</td>
                            <td>800</td>
                            <td>
                                <a data-toggle="collapse" href="#relianceindustries" role="button" aria-expanded="false" aria-controls="relianceindustries">600 </a>
                                <div class="collapse" id="relianceindustries">
                                    <div class="card card-body">
                                        <table class="table table-hover table-striped table-bordered">
                                            <tr>
                                                <th>Team Member</th>
                                                <th style="white-space:nowrap">Estimated Hours</th>
                                                <th style="white-space:nowrap">Hours worked</th>
                                            </tr>
                                            <tr>
                                                <td>Jalpesh Gajjar</td>
                                                <td>250</td>
                                                <td>200</td>
                                            </tr>
                                            <tr>
                                                <td>Sikha Kothari</td>
                                                <td>200</td>
                                                <td>150</td>
                                            </tr>
                                            <tr>
                                                <td>Saini Shah</td>
                                                <td>150</td>
                                                <td>100</td>
                                            </tr>
                                            <tr>
                                                <td>Vishakha Kataria</td>
                                                <td>75</td>
                                                <td>100</td>
                                            </tr>
                                            <tr>
                                                <td>Sonal Patel</td>
                                                <td>25</td>
                                                <td>50</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Tata Sons</td>
                            <td>1000</td>
                            <td><a data-toggle="collapse" href="#tatasons" role="button" aria-expanded="false" aria-controls="tatasons">1100 </a>
                                <div class="collapse" id="tatasons">
                                    <div class="card card-body">
                                        <table class="table table-hover table-striped table-bordered">
                                            <tr>
                                                <th>Team Member</th>
                                                <th style="white-space:nowrap">Estimated Hours</th>
                                                <th style="white-space:nowrap">Hours worked</th>
                                            </tr>
                                            <tr>
                                                <td>Jalpesh Gajjar</td>
                                                <td>190</td>
                                                <td>200</td>
                                            </tr>
                                            <tr>
                                                <td>Sikha Kothari</td>
                                                <td>180</td>
                                                <td>200</td>
                                            </tr>
                                            <tr>
                                                <td>Saini Shah</td>
                                                <td>250</td>
                                                <td>300</td>
                                            </tr>
                                            <tr>
                                                <td>Vishakha Kataria</td>
                                                <td>190</td>
                                                <td>200</td>
                                            </tr>
                                            <tr>
                                                <td>Sonal Patel</td>
                                                <td>190</td>
                                                <td>200</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div></td>
                        </tr>
                        <tr>
                            <td>Infosys</td>
                            <td>800</td>
                            <td>750</td>
                        </tr>
                    </tbody>
                </table>
    		<?php
    	}
    ?>
  </body>
</html>




 