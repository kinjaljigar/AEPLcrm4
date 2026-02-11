<!DOCTYPE html>
<html>
  <head>
     <title>Aashir Engineering | Project Contact Directory</title>
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
									<h3 class="card-title">Contact Directory: Reliance</h3>
									<div class="card-tools">
										<div class="input-group input-group-sm" style="width: 150px;">
											<div class="input-group-append">
												<a href="addContact.php" class="btn btn-block bg-gradient-success btn-lg">ADD CONTACT</a>
											</div>
										</div>
									</div>
								</div>
								<div class="card-body table-responsive p-0">
									<table class="table table-hover  text-nowrap">
										<thead>
											<tr>
												<th>Contact Name</th>
                                                <th>Designation</th>
												<th>Email</th>
												<th>Mobile</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												
												<td>Jalpesh Gajjar</td>
                                                <td>Owner</td>
												<td>ramesh@gmail.com</td>
												<td>9990003339</td>
												<td>
													 <a href="#.php" class="btn bg-gradient-success" title="Edit"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
						                            <a href="#" class="btn bg-gradient-danger" title="Delete"><i class="fa fa-trash" aria-hidden="true"></i></a>
												</td>
											</tr>
											<tr>
												<td>James Bond</td>
                                                <td>Chief Engineer</td>
												<td>jamesbond@gmail.com</td>
												<td>9898989898</td>
												<td>
													<a href="#.php" class="btn bg-gradient-success" title="Edit"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
						                            <a href="#" class="btn bg-gradient-danger" title="Delete"><i class="fa fa-trash" aria-hidden="true"></i></a>
												</td>
											</tr>
											<tr>
												<td>Raj Patel</td>
                                                <td>Supervisor</td>
												<td>rajpatel@gmail.com</td>
												<td>90890989989</td>
												<td>
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




 