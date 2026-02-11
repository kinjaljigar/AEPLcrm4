<!DOCTYPE html>
<html>
  <head>
     <title>Employees | Add Employee</title>
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
									<h3 class="card-title">Employee</h3>
									<div class="card-tools">
										<div class="input-group input-group-sm" style="width: 150px;">
											<div class="input-group-append">
												<a href="addEmployee.php" class="btn btn-block bg-gradient-success btn-lg">ADD EMPLOYEE</a>
											</div>
										</div>
									</div>
								</div>
								<div class="card-body table-responsive p-0">
									<table class="table table-hover text-nowrap">
										<thead>
											<tr>
												<th>Username</th>
												<th>Employee Name</th>
												<th>Email</th>
												<th>Mobile</th>
												<th>Salary/Hr.</th>
												<th>User Type</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td>jgajjar</td>
												<td>Jalpesh Gajjar</td>
												<td>ramesh@gmail.com</td>
												<td>9990003339</td>
												<td>500</td>
												<td>Employee</td>
												<td>
													<a href="employeePerformance.php" class="btn bg-gradient-primary" title="Performance"><i class="fa fa-chart-bar" aria-hidden="true"></i></a>
						                            <a href="#.php" class="btn bg-gradient-success" title="Edit"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
						                            <a href="#" class="btn bg-gradient-danger" title="Delete"><i class="fa fa-trash" aria-hidden="true"></i></a>
												</td>
											</tr>
											<tr>
												<td>Jbond</td>
												<td>James Bond</td>
												<td>jamesbond@gmail.com</td>
												<td>9898989898</td>
												<td>700</td>
												<td>Project Leader</td>
												<td>
													<a href="employeePerformance.php" class="btn bg-gradient-primary" title="Performance"><i class="fa fa-chart-bar" aria-hidden="true"></i></a>
						                            <a href="#.php" class="btn bg-gradient-success" title="Edit"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
						                            <a href="#" class="btn bg-gradient-danger" title="Delete"><i class="fa fa-trash" aria-hidden="true"></i></a>
												</td>
											</tr>
											<tr>
												<td>julian</td>
												<td>Raj Patel</td>
												<td>rajpatel@gmail.com</td>
												<td>90890989989</td>
												<td>1000</td>
												<td>Bim Head</td>
												<td>
													<a href="employeePerformance.php" class="btn bg-gradient-primary" title="Performance"><i class="fa fa-chart-bar" aria-hidden="true"></i></a>
						                            <a href="#.php" class="btn bg-gradient-success" title="Edit"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
						                            <a href="#" class="btn bg-gradient-danger" title="Delete"><i class="fa fa-trash" aria-hidden="true"></i></a>
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
