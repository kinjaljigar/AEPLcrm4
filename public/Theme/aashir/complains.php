<!DOCTYPE html>
<html>
  <head>
     <title>NextView | Complains</title>
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
									<h3 class="card-title">Complains</h3>
									<div class="card-tools">
										<div class="input-group input-group-sm" style="width: 150px;">
											<div class="input-group-append">
												<a href="addCustomer.php" class="btn btn-block bg-gradient-success btn-lg">ADD COMPLAIN</a>
											</div>
										</div>
									</div>
								</div>



								<div class="card card-secondary mt-2">
                      				
                      				<div class="card-body">
				                        <div class="row mb-2">
				                        	
						                    <div class="col-lg-3 col-md-6 col-sm-12">
						                    	<div class="row">
							                    	<div class="col-9">
						                          		<label for="InputMobile">Search by Complain Number</label>
		                        						<input type="text" name="complainNumber" class="form-control" id="InputcomplainNumber" placeholder="Enter Complain number">
						                          	</div>
					                         		
					                         	 	<div class="col-3">
					                         	 		<label for="InputInvoiceDates">.</label><br>
	                        							<button type="button" class="btn bg-gradient-success">SEARCH</button>
					                         	 	</div>
					                         	 </div>
				                         	</div>
				                         	<div class="col-lg-9 col-md-6 col-sm-12">
				                         		<div class="row">
				                         			<div class="form-group col-lg-4 col-md-4 col-sm-12">
						                                <label for="InputSerice Center">Service Center</label>
						                                <select class="form-control" name="serviceCenterID" id="InputServiceCenterId">
						                                  <option value="1">Sales India</option>
						                                  <option value="2">Reliance Digital</option>
						                                  <option value="3">Croma</option>
						                                  <option value="4">Vijay Sales</option>
						                                  <option value="5">Ramesh Electronics</option>
						                                </select>
						                              </div>
						                              <div class="form-group col-lg-4 col-md-4 col-sm-12">
						                              	<label for="InputSerice Center">Status</label>
						                              	<select class="form-control" name="status" id="InputStatus">
						                                  <option value="1">New</option>
						                                  <option value="2">Pending</option>
						                                  <option value="3">Completed By Serice Center</option>
						                                  <option value="4">Completed</option>
						                                </select>
						                              </div>
						                              <div class="form-group col-lg-4 col-md-4 col-sm-12">
						                              	<label for="dd">.</label><br>
	                        							<button type="button" class="btn bg-gradient-success">SEARCH</button>
						                              </div>
				                         		</div>
				                         	</div>
				                        </div>
                      				</div>
                     			</div>


								<div class="card-body table-responsive p-0">
									<table class="table table-hover text-nowrap">
										<thead>
											<tr>
												<th>Complain Number</th>
												<th>Complain Title</th>
												<th>Complain Type</th>
												<th>Complain Date</th>
												<th>Customer Name</th>
												<th>Service Center</th>
												<th>Status</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td>NVC00099</td>
												<td>Installation of New TV</td>
												<td>Installation</td>
												<td>29/05/2020</td>
												<td>Ravi Rajpara</td>
												<td>dCode Service</td>
												<td>New</td>
												<td><a href="viewComplain.php" class="btn bg-gradient-danger">VIEW</a></td>
											</tr>
											<tr>
												<td>NVC00098</td>
												<td>OS need to update</td>
												<td>Complain</td>
												<td>28/05/2020</td>
												<td>Paresh Popat</td>
												<td>Age Electronics</td>
												<td>Pending</td>
												<td><a href="viewComplain.php" class="btn bg-gradient-danger">VIEW</a></td>
											</tr>
											<tr>
												<td>NVC00097</td>
												<td>TV Installation</td>
												<td>Installation</td>
												<td>27/05/2020</td>
												<td>Janak Jamadar</td>
												<td>Shiv Electronics</td>
												<td>Completed By Service Center</td>
												<td><a href="viewComplain.php" class="btn bg-gradient-danger">VIEW</a></td>
											</tr>
											<tr>
												<td>NVC00096</td>
												<td>New app not able to install</td>
												<td>Complain</td>
												<td>26/05/2020</td>
												<td>Dilip Deliwala</td>
												<td>Krishna Electronics</td>
												<td>Completed</td>
												<td><a href="viewComplain.php" class="btn bg-gradient-danger">VIEW</a></td>
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




 