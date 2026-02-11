<!DOCTYPE html>
<html>
  <head>
     <title>Dr. Ila's Ayurved | Special Day Diet</title>
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
									<h3 class="card-title">Special Days</h3>
									<div class="card-tools">
										<div class="input-group input-group-sm" style="width: 150px;">
											<div class="input-group-append">
												<a href="addspecialday.php" class="btn btn-block bg-gradient-success btn-lg">ADD SPECIAL DAY DIET</a>
											</div>
										</div>
									</div>
								</div>
								<div class="card-body table-responsive p-0">
									<table class="table table-hover text-nowrap">
										<thead>
											<tr>
												<th>Special Day</th>
												<th>Date</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td>Makarsankranit</td>
												<td>14/01/2020</td>
												<td><button type="button" class="btn bg-gradient-success">EDIT DIET</button></td>
											</tr>
											<tr>
												<td>Agiyaras</td>
												<td>21/01/2020</td>
												<td><button type="button" class="btn bg-gradient-success">EDIT DIET</button></td>
											</tr>
											<tr>
												<td>Ram Navmi</td>
												<td>03/03/2020</td>
												<td><button type="button" class="btn bg-gradient-success">EDIT DIET</button></td>
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




 