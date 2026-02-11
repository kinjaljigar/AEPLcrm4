<!DOCTYPE html>
<html>
  <head>
     <title>NextView | Add Dealer</title>
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
									<h3 class="card-title">Invoices</h3>
									<div class="card-tools">
										<div class="input-group input-group-sm" style="width: 150px;">
											<div class="input-group-append">
												<a href="addInvoice.php" class="btn btn-block bg-gradient-success btn-lg">ADD INVOICE</a>
											</div>
										</div>
									</div>
								</div>


								<div class="card card-secondary mt-2">
                      				<div class="card-header"><h3 class="card-title">FILTER</h3></div>
                      				<div class="card-body">
				                        <div class="row mb-2">
				                        	<div class="col-lg-6 col-md-12 col-sm-12 ">
				                        		<div class="row">
						                        	<div class="col-lg-4 col-md-4 col-sm-12">
						                          	 	<label for="InputDistributor">Distributor</label>
		                        						<select class="form-control" name="distributorid" id="InputDistributor">
								                          <option value="1">Sales India</option>
								                          <option value="2">Vijay Sales</option>
								                          <option value="3">Croma</option>
								                          <option value="4">Reliance Digital</option>
								                        </select>
						                          	</div>
						                          	<div class="col-lg-4 col-md-4 col-sm-12">
						                          		<label for="InputDealer">Dealer</label>
		                        						<select class="form-control" name="dealerid" id="InputDealer">
								                          <option value="1">Chirag Shah</option>
								                          <option value="2">Ramesh Patel</option>
								                          <option value="3">Vaibhv Gandhi</option>
								                          <option value="4">Mukesh Ambani</option>
								                        </select>
						                          	</div>
						                          	<div class="col-lg-4 col-md-4 col-sm-12">
						                          		<label for="InputScheme">Scheme</label>
		                        						<select class="form-control" name="offerid" id="InputScheme">
								                          <option value="1">Buy 6 TV Get 1 free</option>
								                          <option value="2">But 10 TV Get 2 free</option>
								                        </select>
						                          	</div>
						                        </div>
						                    </div>
						                    <div class="col-lg-6 col-md-12 col-sm-12">
						                    	<div class="row">
							                    	<div class="col-lg-4 col-md-4 col-sm-12">
						                          		<label for="InputInvoiceDate">Start Date</label>
		                        						<input type="text" name="invoicedate" class="form-control" id="InputInvoiceDate" placeholder="Start Date">
						                          	</div>
					                         		<div class="col-lg-4 col-md-4 col-sm-12">
					                         	 		<label for="InputInvoiceDate">End Date</label>
	                        							<input type="text" name="invoicedate" class="form-control" id="InputInvoiceDate" placeholder="End Date">
	                        							
					                         	 	</div>
					                         	 	<div class="col-lg-4 col-md-4 col-sm-12">
					                         	 		<label for="InputInvoiceDate">.</label><br>
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
												<th>Invioce #</th>
												<th>Invoice Date</th>
												<th>Invoice Amount</th>
												<th>Distributor</th>
												<th>Scheme</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td>NVS00CC002</td>
												<td>1/5/2020</td>
												<td>575000</td>
												<td>NC001</td>
												<td>Buy 10 TV Get 2 Free</td>
												<td><img src="dist/img/invoice.jpg" width="50" height="50" onclick="openinvoice('dist/img/invoice.jpg')"></td>
												<td><button type="button" class="btn bg-gradient-success">EDIT</button></td>
											</tr>
											<tr>
												<td>NVS00CC003</td>
												<td>10/5/2020</td>
												<td>500000</td>
												<td>NC001</td>
												<td>Buy 6 TV Get 1 Free</td>
												<td><img src="dist/img/invoice.jpg" width="50" height="50" onclick="openinvoice('dist/img/invoice.jpg')"></td>
												<td><button type="button" class="btn bg-gradient-success">EDIT</button></td>
											</tr>
											<tr>
												<td>NVS00CC004</td>
												<td>10/5/2020</td>
												<td>500000</td>
												<td>NC001</td>
												<td>Buy 6 TV Get 1 Free</td>
												<td><img src="dist/img/invoice.jpg" width="50" height="50" onclick="openinvoice('dist/img/invoice.jpg')"></td>
												<td><button type="button" class="btn bg-gradient-success">EDIT</button></td>
											</tr>
											<tr>
												<td>NVS00CC005</td>
												<td>10/5/2020</td>
												<td>500000</td>
												<td>NC001</td>
												<td>Buy 6 TV Get 1 Free</td>
												<td><img src="dist/img/invoice.jpg" width="50" height="50" onclick="openinvoice('dist/img/invoice.jpg')"></td>
												<td><button type="button" class="btn bg-gradient-success">EDIT</button></td>
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




 