<!DOCTYPE html>
<html>
  <head>
     <title>Aashir Engineering | Timesheet</title>
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
									<h3 class="card-title">Daily Timesheet: Jalpesh Gajjar | Date: 18/09/2020</h3>
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
                                                displayTimesheetRow('09:00 am to 10:00 am');
                                                displayTimesheetRow('10:00 am to 11:00 am');
                                                displayTimesheetRow('11:00 am to 12:00 am');
                                                displayTimesheetRow('12:00 pm to 01:00 pm');
                                                displayTimesheetRow('01:00 pm to 02:00 pm');
                                                displayTimesheetRow('02:00 pm to 03:00 pm');
                                                displayTimesheetRow('03:00 pm to 04:00 pm');
                                                displayTimesheetRow('04:00 pm to 05:00 pm');
                                                displayTimesheetRow('05:00 pm to 06:00 pm');
                                            ?>
                                        <tr>
                                            <td colspan="4"><button id="addprojectexpense" type="button" class="btn btn-primary btn-flats">SUBMIT</button></td>
                                        </tr>
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

        function displayTimesheetRow($workhour){
            ?>
                <tr>
                    <td>
                        <select  class="form-control">
                            <option value="0">Select Project</option>
                            <option value="1">Project 1 Title goes here</option>
                            <option value="1">Project 2 Title</option>
                            <option value="1">Project 3 Title</option>
                            <option value="1">Project 4 Title</option>
                        </select>
                    </td>
                    <td>
                        <select  class="form-control">
                            <option value="0">Select Task</option>
                            <option value="1">Task 1 Title goes here</option>
                            <option value="1">Task 2 Title</option>
                            <option value="1">Task 3 Title</option>
                            <option value="1">Task 4 Title</option>
                        </select>
                    </td>
                    <td>
                        <table>
                            <tr>
                                <td>From</td>
                                <td>To</td>
                            </tr>
                            <tr>
                                <td> <?php selectTime() ?> </td>
                                <td><?php selectTime() ?></td>
                            </tr>
                            <tr>
                                <td colspan="2"><input type="checkbox" > On Leave</td>
                            </tr>
                        </table>
                       
                    </td>
                                                
                    <td>
                    <textarea name="emailbody" class="form-control" id="emailbody" placeholder="Enter your report" rows="2"></textarea>
                    </td>
                    
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
 