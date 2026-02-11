<!DOCTYPE html>
<html>
  <head>
     <title>Aashir Engnieering | Projects</title>
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
									<h3 class="card-title">Projects</h3>
									<div class="card-tools">
										<div class="input-group input-group-sm" style="width: 150px;">
											<div class="input-group-append">
												<a href="addProject.php" class="btn btn-block bg-gradient-success btn-lg">ADD PROJECTS</a>
											</div>
										</div>
									</div>
								</div>
								<div class="card-body table-responsive p-0">
									<table class="table table-hover text-nowrap">
										<thead>
											<tr>
												<th>Project Number</th>
												<th>Project Name</th>
												<th>Address</th>
												<th>Cost</th>
												<th>Expense</th>
												<th>Profit/Loss</th>
												<th>Status</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td>PRJ001</td>
												<td>Reliance</td>
												<td>Reliance Petrochemicals, Jamnagar</td>
												<td>5,00,00,000</td>
												<td>3,00,00,000</td>
												<td>2,00,00,000</td>
												<td>Active</td>
												<td>
													<a href="projectDetails.php" class="btn bg-gradient-primary"><i class="fa fa-eye" aria-hidden="true"></i></a>
						                            <a href="editTaskDetails.php" class="btn bg-gradient-success"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
						                            <a href="#" class="btn bg-gradient-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
													<a href="contacts.php" class="btn bg-gradient-warning"><i class="fa fa-phone" aria-hidden="true"></i></a>
												</td>
											</tr>
											<tr>
												<td>PRJ002</td>
												<td>Narmada Canal</td>
												<td>Gandhinagar</td>
												<td>6,00,00,000</td>
												<td>3,50,00,000</td>
												<td>2,50,00,000</td>
												<td>Completed</td>
												<td>
													<a href="projectDetails.php" class="btn bg-gradient-primary"><i class="fa fa-eye" aria-hidden="true"></i></a>
						                            <a href="editTaskDetails.php" class="btn bg-gradient-success"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
						                            <a href="#" class="btn bg-gradient-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
													<a href="#" class="btn bg-gradient-warning"><i class="fa fa-phone" aria-hidden="true"></i></a>
												</td>
											</tr>
											<tr style="background-color:#fce3de;">
												<td>PRJ003</td>
												<td>Surat Birdge</td>
												<td>Surat Nagarpalica</td>
												<td>3,00,00,000</td>
												<td>3,10,00,000</td>
												<td>-10,00,000</td>
												<td>Completed</td>
												<td>
													<a href="projectDetails.php" class="btn bg-gradient-primary"><i class="fa fa-eye" aria-hidden="true"></i></a>
						                            <a href="editTaskDetails.php" class="btn bg-gradient-success"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
						                            <a href="#" class="btn bg-gradient-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
													<a href="#" class="btn bg-gradient-warning"><i class="fa fa-phone" aria-hidden="true"></i></a>
												</td>
											</tr>
											<tr>
												<td>PRJ004</td>
												<td>Ahmedabad One Mall</td>
												<td>Vastrapur, Ahmedabad</td>
												<td>5,00,00,000</td>
												<td>N/A</td>
												<td>N/A</td>
												<td>On Hold</td>
												<td>
													<a href="projectDetails.php" class="btn bg-gradient-primary"><i class="fa fa-eye" aria-hidden="true"></i></a>
						                            <a href="editTaskDetails.php" class="btn bg-gradient-success"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
						                            <a href="#" class="btn bg-gradient-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
													<a href="#" class="btn bg-gradient-warning"><i class="fa fa-phone" aria-hidden="true"></i></a>
												</td>
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




 