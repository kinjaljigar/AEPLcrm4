<!DOCTYPE html>
<html>
  <head>
     <title>NextView | Schemes</title>
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
									<h3 class="card-title">Schemes</h3>
									<div class="card-tools">
										<div class="input-group input-group-sm" style="width: 150px;">
											<div class="input-group-append">
												<a href="addoffer.php" class="btn btn-block bg-gradient-success btn-lg">ADD SCHEME</a>
											</div>
										</div>
									</div>
								</div>
								<div class="card-body table-responsive p-0">
									<table class="table table-hover text-nowrap">
										<thead>
											<tr>
												<th>Scheme Name</th>
												<th>Scheme Image</th>
												<th>Start Date</th>
												<th>End Date</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td>Buy 4 55" TV get 1 34" TV Free</td>
												<td><img src="dist/img/offer.png" width="50" height="50" onclick="openinvoice('dist/img/offer.png')"></td>
												<td>1/5/2020</td>
												<td>30/5/2020</td>
												<td><button type="button" class="btn bg-gradient-success">EDIT</button></td>
											</tr>
											<tr>
												<td>Buy 6 TV Get 1 Free</td>
												<td><img src="dist/img/offer.png" width="50" height="50" onclick="openinvoice('dist/img/offer.png')"></td>
												<td>1/6/2020</td>
												<td>30/6/2020</td>
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




 