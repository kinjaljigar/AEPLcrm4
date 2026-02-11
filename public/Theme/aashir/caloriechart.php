<!DOCTYPE html>
<html>
  <head>
     <title>Dr. Ila's Ayurveda | Calorie Chart</title>
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
									<h3 class="card-title">Calorie Chart</h3>
									<div class="card-tools">
										<div class="input-group input-group-sm" style="width: 150px;">
											<div class="input-group-append">
												<a href="addFood.php" class="btn btn-block bg-gradient-success btn-lg">ADD FOOD</a>
											</div>
										</div>
									</div>
								</div>
								<div class="card-body table-responsive p-0">
									<table class="table table-hover text-nowrap">
										<thead>
											<tr>
												<th>Food</th>
												<th>Calorie</th>
												<th>Fat</th>
												<th>Protien</th>
												<th>Crabs</th>
												<th>Fiber</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td>Walnus (100 gm)</td><td>654</td><td>72g</td><td>9g</td><td>14g</td><td>10g</td>
												<td>
													<button type="button" class="btn bg-gradient-success">EDIT</button>
													<a href="viewDoctor.php" class="btn bg-gradient-danger">DELETE</a>
												</td>
											</tr>
                                            <tr>
												<td>Hazelnuts (100 gm)</td><td>628</td><td>61g</td><td>15g</td><td>17g</td><td>10g</td>
												<td>
													<button type="button" class="btn bg-gradient-success">EDIT</button>
													<a href="viewDoctor.php" class="btn bg-gradient-danger">DELETE</a>
												</td>
											</tr>
                                            <tr>
												<td>Sunflower Seeds (100 gm)</td><td>584</td><td>51g</td><td>21g</td><td>22g</td><td>12g</td>
												<td>
													<button type="button" class="btn bg-gradient-success">EDIT</button>
													<a href="viewDoctor.php" class="btn bg-gradient-danger">DELETE</a>
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




 