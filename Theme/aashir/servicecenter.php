<!DOCTYPE html>
<html>
  <head>
     <title>NextView | Service Centers</title>
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
									<h3 class="card-title">Service Centers</h3>
									<div class="card-tools">
										<div class="input-group input-group-sm" style="width: 150px;">
											<div class="input-group-append">
												<a href="addServiceCenter.php" class="btn btn-block bg-gradient-success btn-lg">ADD SERVICE CENTER</a>
											</div>
										</div>
									</div>
								</div>
								<div class="card-body table-responsive p-0">
									<table class="table table-hover text-nowrap">
										<thead>
											<tr>
												<th>Company Name</th>
												<th>Contact Person</th>
												<th>Email</th>
												<th>Mobile</th>
												<th>Zone</th>
												<th>City</th>
												<th>Village</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												
												<td>Sales India</td>
												<td>Ramesh Patel</td>
												<td>ramesh@gmail.com</td>
												<td>9990003339</td>
												<td>Central Gujarat</td>
												<td>Ahmedabad</td>
												<td>Ahmedabad</td>
												<td>
													<button type="button" class="btn bg-gradient-success">EDIT</button>
													<button type="button" class="btn bg-gradient-primary">VIEW</button>
												</td>
											</tr>
											<tr>
												
												<td>Vijay Sales</td>
												<td>Vaibhav Gandhi</td>
												<td>vaibhv@gmail.com</td>
												<td>3432438778</td>
												<td>North Gujarat</td>
												<td>Mahesana</td>
												<td>Mahesana</td>
												<td>
													<button type="button" class="btn bg-gradient-success">EDIT</button>
													<button type="button" class="btn bg-gradient-primary">VIEW</button>
												</td>
											</tr>
											<tr>
												
												<td>Croma</td>
												<td>Chirag Shah</td>
												<td>chirag@gmail.com</td>
												<td>98830003339</td>
												<td>Central Gujarat</td>
												<td>Ahmedabad</td>
												<td>Ahmedabad</td>
												<td>
													<button type="button" class="btn bg-gradient-success">EDIT</button>
													<button type="button" class="btn bg-gradient-primary">VIEW</button>
												</td>
											</tr>
											<tr>
												
												<td>Reliance Digital</td>
												<td>Mukesh Ambani</td>
												<td>reliance@gmail.com</td>
												<td>75757575757</td>
												<td>Saurashtra</td>
												<td>Rajkot</td>
												<td>Rajkot</td>
												<td>
													<button type="button" class="btn bg-gradient-success">EDIT</button>
													<button type="button" class="btn bg-gradient-primary">VIEW</button>
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




 