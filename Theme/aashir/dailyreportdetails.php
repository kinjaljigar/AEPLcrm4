<!DOCTYPE html>
<html>
  <head>
     <title>Aashir Engineering | Daily Report Details</title>
    <?php require("head.php"); ?>
    <script type="text/javascript">
    	function openinvoice(url){
    		window.open(url, "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=200,left=200,width=600,height=800");
    	}
    </script>
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
									<h3 class="card-title">Daily Report: Jalpesh Gajjar | Date: 18/09/2020</h3>
									<div class="card-tools">
										<div class="input-group input-group-sm" style="width: 150px;">
											<div class="input-group-append">
												
											</div>
										</div>
									</div>
								</div>
								<div class="card-body table-responsive p-0">
                                   <table class="table table-hover table-striped table-bordered">
										<thead>
											<tr>
												<th width="20%">Project</th>
                                                <th width="20%">Task</th>
                                                <th width="15%">Time</th>
                                                <th width="45%">Comment</th>
											</tr>
										</thead>
										<tbody>
											<?php
                                                displayTimesheetRow();
                                                
                                            ?>
                                        
										</tbody>
									</table>
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
  </body>
</html>



<?php 

        function displayTimesheetRow(){
            ?>
                <tr>
                    <td>Project 1 Title goes here</td>
                    <td>Task 1 Title goes here</td>
                    <td>From: 9:00 To: 10:00</td>
                    <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent ornare massa eget vehicula iaculis. Suspendisse quis aliquet justo. Sed non arcu nibh. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td> 
                </tr>
                <tr>
                    <td>Project 2 Title goes here</td>
                    <td>Task 2 Title goes here</td>
                    <td>From: 10:00 To: 11:00</td>
                    <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent ornare massa eget vehicula iaculis. Suspendisse quis aliquet justo. Sed non arcu nibh. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td> 
                </tr>
                <tr>
                    <td>Project 1 Title goes here</td>
                    <td>Task 2 Title goes here</td>
                    <td>From: 11:00 To: 12:00</td>
                    <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent ornare massa eget vehicula iaculis. Suspendisse quis aliquet justo. Sed non arcu nibh. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td> 
                </tr>
                <tr>
                    <td>Project 1 Title goes here</td>
                    <td>Task 3 Title goes here</td>
                    <td>From: 12:00 To: 1:00</td>
                    <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent ornare massa eget vehicula iaculis. Suspendisse quis aliquet justo. Sed non arcu nibh. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td> 
                </tr>
                <tr>
                    <td>Project 2 Title goes here</td>
                    <td>Task 2 Title goes here</td>
                    <td>From: 2:00 To: 3:00</td>
                    <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent ornare massa eget vehicula iaculis. Suspendisse quis aliquet justo. Sed non arcu nibh. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td> 
                </tr>
                <tr>
                    <td>Project 2 Title goes here</td>
                    <td>Task 3 Title goes here</td>
                    <td>From: 3:00 To: 4:00</td>
                    <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent ornare massa eget vehicula iaculis. Suspendisse quis aliquet justo. Sed non arcu nibh. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td> 
                </tr>
                <tr>
                    <td>Project 1 Title goes here</td>
                    <td>Task 4 Title goes here</td>
                    <td>From: 4:00 To: 5:00</td>
                    <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent ornare massa eget vehicula iaculis. Suspendisse quis aliquet justo. Sed non arcu nibh. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td> 
                </tr>
                <tr>
                    <td>Project 1 Title goes here</td>
                    <td>Task 5 Title goes here</td>
                    <td>From: 5:00 To: 6:00</td>
                    <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent ornare massa eget vehicula iaculis. Suspendisse quis aliquet justo. Sed non arcu nibh. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td> 
                </tr>
                
            <?php
        }

        function selectTime(){
?>

            <select  class="form-control">
                <option value="">9:00</option>
                <option value="">9:15</option>
                <option value="">9:30</option>
                <option value="">9:45</option>
                <option value="">10:00</option>
                <option value="">10:15</option>
                <option value="">10:30</option>
                <option value="">10:45</option>
                <option value="">11:00</option>
                <option value="">11:15</option>
                <option value="">11:30</option>
                <option value="">11:45</option>
                <option value="">12:00</option>
                <option value="">12:15</option>
                <option value="">12:30</option>
                <option value="">12:45</option>
                <option value="">13:00</option>
                <option value="">13:15</option>
                <option value="">13:30</option>
                <option value="">13:45</option>
                <option value="">14:00</option>
                <option value="">14:15</option>
                <option value="">14:30</option>
                <option value="">14:45</option>
                <option value="">15:00</option>
                <option value="">15:15</option>
                <option value="">15:30</option>
                <option value="">15:45</option>
                <option value="">16:00</option>
                <option value="">16:15</option>
                <option value="">16:30</option>
                <option value="">16:45</option>
                <option value="">17:00</option>
                <option value="">17:15</option>
                <option value="">17:30</option>
                <option value="">17:45</option>
                <option value="">18:00</option>
                
            </select>
        <?php   } ?>
 