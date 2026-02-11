<!DOCTYPE html>
<html>
  <head>
     <title>NextView | Products</title>
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
									<h3 class="card-title">Products</h3>
									<div class="card-tools">
										<div class="input-group input-group-sm" style="width: 150px;">
											<div class="input-group-append">
												<a href="addproduct.php" class="btn btn-block bg-gradient-success btn-lg">ADD PRODUCT</a>
											</div>
										</div>
									</div>
								</div>
								<div class="card-body table-responsive p-0">
									<table class="table table-hover text-nowrap">
										<thead>
											<tr>
												<th>Model #</th>
												<th>Serial #</th>
												<th>Quantity</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td>MD001</td>
												<td>SR33003435303434353</td>
												<td>100</td>
												<td><button type="button" class="btn bg-gradient-success">EDIT</button></td>
											</tr>
											<tr>
												<td>MD002</td>
												<td>SR7657345343353</td>
												<td>55</td>
												<td><button type="button" class="btn bg-gradient-success">EDIT</button></td>
											</tr>
											<tr>
												<td>MD003</td>
												<td>43564565755454</td>
												<td>90</td>
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




 