<!DOCTYPE html>
<html>
  <head>
     <title>Aashir Engineering | Employee Performance</title>
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
									<h3 class="card-title">Jalpesh Gajjar's Performance</h3>
									<div class="card-tools">
										<div class="input-group input-group-sm" style="width: 150px;">
											<div class="input-group-append">
												<a href="employees.php" class="btn btn-block bg-gradient-success btn-lg">EMPLOYEE LIST</a>
											</div>
										</div>
									</div>
								</div>
								<div class="text-center mt-2 mb-2">
									<input type="radio" checked name="projecttype"> Active Projects 
									<input type="radio" name="projecttype" class="ml-3"> Completed Projects 
									<input type="submit" name="btsearch" value="Submit" class="btn btn-primary ml-3">
								</div>
								</div>
								<div class="card-body table-responsive p-0">
									<div class="accordion" id="accordionExample">
									  <?php 
									  	employeePerformance('1','Project 1 Title'); 
									  	employeePerformance('2','Project 2 Title'); 
									  	employeePerformance('3','Project 3 Title'); 
									  	employeePerformance('4','Project 4 Title'); 
									  	employeePerformance('5','Project 5 Title'); 
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

    	function employeePerformance($num,$ProjectTitle){
    		?>
    		<div class="card">
			    <div class="card-header bg-light" id="heading<?=$num;?>">
			    <h5 class="mb-0">
						        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse<?=$num;?>" aria-expanded="true" aria-controls="collapse<?=$num;?>">
						          <?= $ProjectTitle; ?>
						        </button>
						    </h5>
			    	
			    </div>

			    <div id="collapse<?=$num;?>" class="collapse" aria-labelledby="heading<?=$num;?>" data-parent="#accordionExample">
			      <div class="card-body">
			        <table class="table table-hover table-striped text-nowrap" width="100%">
                        <tr>
                          <th class="text-center">Total Tasks Assigned</th>
                          <th class="text-center">Active Tasks</th>
                          <th class="text-center">Completed Tasks</th>
                          <th class="text-center">Completed on Time</th>
                          <th class="text-center">Missed Deadline</th>
                          
                        </tr>
                        <tr>
                          <td class="text-center">12</td>
                          <td class="text-center">5</td>
                          <td class="text-center">7</td>
                          <td class="text-center">5</td>
                          <td class="text-center">2</td>
                         </tr>
                      </table>
                      <?php employeeTasks($num); ?>
			      </div>
			    </div>
			  </div>
    		<?php
    	}
    

    function employeeTasks($num){
    		?>
    		<div class="card">
			    

			    <div>
			      <div class="card-body">
			        <table class="table table-hover table-striped text-nowrap" width="100%">
                        <tr>
                          <th>Sr. No.</th>
                          <th>Task Title</th>
                          <th>Priority</th>
                          <th>Posted Date</th>
                          <th>Posted By</th>
                          <th>Status</th>
                          <th>Action</th>
                        </tr>
                        <tr style="background-color: #f5da88;">
                          <td>1</td>
                          <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
                          <td>Highest</td>
                          <td>31/08/2020</td>
                          <td>Dharmesh</td>
                          <td>Inprogress</td>
                          <td>
                            <a href="viewTaskDetails.php" class="btn bg-gradient-primary"><i class="fa fa-eye" aria-hidden="true"></i></a>
                            <a href="editTaskDetails.php" class="btn bg-gradient-success"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
                            <a href="#" class="btn bg-gradient-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
                          </td>
                        </tr>
                        <tr style="background-color:#f9cdcd;">
                          <td>2</td>
                          <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
                          <td>Highest</td>
                          <td>31/08/2020</td>
                          <td>Dharmesh</td>
                          <td>Inprogress <br>With Delay</td>
                          <td>
                            <a href="viewTaskDetails.php" class="btn bg-gradient-primary"><i class="fa fa-eye" aria-hidden="true"></i></a>
                            <a href="editTaskDetails.php" class="btn bg-gradient-success"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
                            <a href="#" class="btn bg-gradient-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
                          </td>
                        </tr>
                        <tr style="background-color: #b8fac7;">
                          <td>3</td>
                          <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
                          <td>Highest</td>
                          <td>31/08/2020</td>
                          <td>Dharmesh</td>
                          <td>Completed <br>On/before time</td>
                          <td>
                            <a href="viewTaskDetails.php" class="btn bg-gradient-primary"><i class="fa fa-eye" aria-hidden="true"></i></a>
                            <a href="editTaskDetails.php" class="btn bg-gradient-success"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
                            <a href="#" class="btn bg-gradient-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
                          </td>
                        </tr>
                        <tr class="bg-danger">
                          <td>4</td>
                          <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
                          <td>Highest</td>
                          <td>31/08/2020</td>
                          <td>Dharmesh</td>
                          <td>Completed<br>With Delay</td>
                          <td>
                            <a href="viewTaskDetails.php" class="btn bg-gradient-primary"><i class="fa fa-eye" aria-hidden="true"></i></a>
                            <a href="editTaskDetails.php" class="btn bg-gradient-success"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
                            <a href="#" class="btn bg-gradient-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
                          </td>
                        </tr>
                        <tr>
                          <td>5</td>
                          <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
                          <td>Highest</td>
                          <td>31/08/2020</td>
                          <td>Dharmesh</td>
                          <td>New</td>
                          <td>
                            <a href="viewTaskDetails.php" class="btn bg-gradient-primary"><i class="fa fa-eye" aria-hidden="true"></i></a>
                            <a href="editTaskDetails.php" class="btn bg-gradient-success"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
                            <a href="#" class="btn bg-gradient-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
                          </td>
                        </tr>
                        
                       
                      </table>
			      </div>
			    </div>
			  </div>
    		<?php
    	}
    ?>
  </body>
</html>




 