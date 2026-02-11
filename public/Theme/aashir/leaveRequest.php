<!DOCTYPE html>
<html>
  <head>
     <title>Aashir Engineering | Leave Request</title>
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
									<h3 class="card-title">Leave Request</h3>
									<div class="card-tools">
										<div class="input-group input-group-sm" style="width: 150px;">
											<div class="input-group-append">
												
											</div>
										</div>
									</div>
								</div>
								<div class="card-body table-responsive p-0">
									<table class="table table-hover table-striped table-bordered">
										<thead>
											<tr>
												<th>Employee</th>
                                                <th>Posted Date</th>
                                                <th>From</th>
                                                <th>To</th>
												<th>Message</th>
												<th>Status</th>
                                                <th>Action</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td>Tejas Sagar</td>
                                                <td>2/09/2020</td>
                                                <td>15/09/2020</td>
                                                <td>20/09/2020</td>
                                                <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna... </td>
                                                <td>Approved</td>
												<td>
													
													<a href="replytoLeaveRequest.php" class="btn bg-gradient-success" title="Edit"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
						                            <a href="#" class="btn bg-gradient-danger" title="Delete"><i class="fa fa-trash" aria-hidden="true"></i></a>
												</td>
											</tr>
                                            <tr>
												<td>Kamlesh Mehta</td>
                                                <td>2/09/2020</td>
                                                <td>15/09/2020</td>
                                                <td>20/09/2020</td>
                                                <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna... </td>
                                                <td>Declined</td>
												<td>
													<a href="replytoLeaveRequest.php" class="btn bg-gradient-success" title="Edit"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
						                            <a href="#" class="btn bg-gradient-danger" title="Delete"><i class="fa fa-trash" aria-hidden="true"></i></a>
												</td>
											</tr>
                                            <tr>
												<td>Mayank Raval</td>
                                                <td>2/09/2020</td>
                                                <td>15/09/2020</td>
                                                <td>20/09/2020</td>
                                                <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna... </td>
                                                <td>New</td>
												<td>
													<a href="replytoLeaveRequest.php" class="btn bg-gradient-success" title="Edit"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
						                            <a href="#" class="btn bg-gradient-danger" title="Delete"><i class="fa fa-trash" aria-hidden="true"></i></a>
												</td>
											</tr>
                                            <tr>
												<td>Vaibhav Gandhi</td>
                                                <td>2/09/2020</td>
                                                <td>15/09/2020</td>
                                                <td>20/09/2020</td>
                                                <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna... </td>
                                                <td>New</td>
												<td>
													<a href="replytoLeaveRequest.php" class="btn bg-gradient-success" title="Edit"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
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




 