<!DOCTYPE html>
  <html>
    <head>
      <title>Aashir Engineering | Dashboard</title>
      <?php require("head.php"); ?>
    </head>
    <body class="hold-transition sidebar-mini layout-fixed">
        <div class="wrapper">
          <?php 
          require("nav.php"); 
          require("sidebar.php");
          ?>
        
            <div class="content-wrapper">
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1 class="m-0 text-dark">Dashboard</h1>
                            </div>
                            <div class="col-sm-6">
                                <ol class="breadcrumb float-sm-right">
                                   <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                                </ol>
                            </div><!-- /.col -->
                          </div><!-- /.row -->

                          <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <div class="row">
                                    <div class="col-lg-3 col-md-3 col-sm-12">
                                        <div class="container">
                                            <div class="card">
                                                <div class="card-header">Total Projects</div>
                                                <div class="card-body"><b>300</b></div> 
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3 col-sm-12">
                                        <div class="container">
                                            <div class="card">
                                                <div class="card-header">Active Projects</div>
                                                <div class="card-body"><b>200</b></div> 
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3 col-sm-12">
                                        <div class="container">
                                            <div class="card">
                                                <div class="card-header">Completed Projects</div>
                                                <div class="card-body"><b>100</b></div> 
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3 col-sm-12">
                                        <div class="container">
                                            <div class="card">
                                                <div class="card-header">Total Employees</div>
                                                <div class="card-body"><b>100</b></div> 
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                
                            </div>

                           
                           </div>
                           
                           <section class="content">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card card-primary">
                                                <div class="card-header">
                                                    <h3 class="card-title">Employee Present Departmentwise</h3>
                                                    <div class="card-tools">
                                                        <div class="input-group input-group-sm" style="width: 150px;">
                                                            <div class="input-group-append">
                                                                
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body table-responsive p-0">
                                                    <table class="table table-hover table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th width="33%" class="text-center">Architecture</th>
                                                                <th width="33%" class="text-center">MEPF</th>
                                                                <th width="34%" class="text-center">Admin</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td class="text-center"><a href="employees.php">30</a></td>
                                                                <td class="text-center"><a href="employees.php">23</a></td>
                                                                <td class="text-center"><a href="employees.php">20</a></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card card-primary">
                                                <div class="card-header">
                                                    <h3 class="card-title">Projects under watch</h3>
                                                    <div class="card-tools">
                                                        <div class="input-group input-group-sm" style="width: 150px;">
                                                            <div class="input-group-append">
                                                                
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body table-responsive p-0">
                                                    <table class="table table-hover table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Project</th>
                                                                <th>Start Date</th>
                                                                <th>Total Cost</th>
                                                                <th>Expenses</th>
                                                                <th>Profit/Loss</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>Reliance Industries</td>
                                                                <td>21/07/2020</td>
                                                                <td>2,00,00,000</td>
                                                                <td>1,50,00,000</td>
                                                                <td>50,00,000</td>
                                                                <td>Completed</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Iscon Bridge</td>
                                                                <td>11/05/2020</td>
                                                                <td>2,50,00,000</td>
                                                                <td>1,50,00,000</td>
                                                                <td>1,00,00,000</td>
                                                                <td>Completed</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Vidhan Sabha</td>
                                                                <td>9/06/2020</td>
                                                                <td>5,50,00,000</td>
                                                                <td>2,50,00,000</td>
                                                                <td>2,50,00,000</td>
                                                                <td>Active</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                                                    <table class="table table-hover table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Employee Name</th>
                                                                <th>Application Date</th>
                                                                <th>Leave Start Date</th>
                                                                <th>Leave End Date</th>
                                                                <th># of Days</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>Rohan Patel</td>
                                                                <td>27/08/2020</td>
                                                                <td>05/09/2020</td>
                                                                <td>08/09/2020</td>
                                                                <td>3</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Jalpesh Gajjar</td>
                                                                <td>25/08/2020</td>
                                                                <td>01/09/2020</td>
                                                                <td>10/09/2020</td>
                                                                <td>10</td>
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
                </div>
            </div>
        </div>
    </div>
    <?php require("footer.php"); ?>
</div>
<!-- ./wrapper -->
<?php require("addjs.php") ?>
</body>
</html>
