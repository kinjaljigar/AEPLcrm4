<!DOCTYPE html>
<html>
  <head>
     <title>Aashir Engineering | Employee Leave Report</title>
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
									<h3 class="card-title">Leave Report : From 1st April 2020 to 19th September 2020</h3>
									<div class="card-tools">
										<div class="input-group input-group-sm">
											<div class="input-group-append">
												
											</div>
										</div>
									</div>
								</div>
								<div class="card-body table-responsive p-0">
                                   <table class="table table-hover table-striped table-bordered" >
										<thead>
											<tr>
												<th width="50%">Employee Name</th>
                                                <th width="50%">Leave Taken</th>
											</tr>
										</thead>
										<tbody>
											<tr>
                                                <td>Jalpesh Gajjer</td>
                                                <td>10</td>
                                            </tr>
                                            <tr>
                                                <td>Nikunj Patel</td>
                                                <td>5</td>
                                            </tr>
                                            <tr>
                                                <td>Vaibhav Gandhi</td>
                                                <td>8</td>
                                            </tr>
                                            <tr>
                                                <td>Killol Kamdar</td>
                                                <td>9</td>
                                            </tr>
                                            <tr>
                                                <td>Alkesh Patel</td>
                                                <td>12</td>
                                            </tr>
                                            <tr>
                                                <td>Chirag Shah</td>
                                                <td>8</td>
                                            </tr>
                                            <tr>
                                                <td>Manoj Makwana</td>
                                                <td>7</td>
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
