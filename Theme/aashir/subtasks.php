<!DOCTYPE html>
<html>
  <head>
    <title>Aashir Engineering | Sub Tasks</title>
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
              <div class="col-md-12">
                <div class="card card-primary">
                  <div class="card-header">
                    <h3 class="card-title">PARENT TASK: Lorem ipsum dolor sit amet, consectetur adipiscing elit.</h3>
                    <div class="card-tools">
                      <div class="input-group input-group-sm" style="width: 150px;">
                        <div class="input-group-append">
                          <a href="projects.php" class="btn btn-block bg-gradient-success btn-lg">TASK LIST</a>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                </div>
              </div>
            </div>
          </div>
        </section>

        <section id="prjtabs" class="content">
          
 <div class="container-fluid">
              <div class="row">
                <div class="col-12 ">
                  <nav>
                    <div class="nav nav-tabs nav-fill" id="nav-tab" role="tablist">
                      <a class="nav-item nav-link  active" id="nav-tasks-tab" data-toggle="tab" href="#nav-tasks" role="tab" aria-controls="nav-tasks" aria-selected="false">Sub Tasks List</a>
                    </div>
                  </nav>
                  <div class="tab-content py-3 px-3 px-sm-0" id="nav-tabContent">
                    
                    <div class="tab-pane fade show active" id="nav-tasks" role="tabpanel" aria-labelledby="nav-tasks-tab">
                      <div style="width: auto;float: right;margin-bottom: 10px;">
                        <a href="addSubTask.php" class="btn bg-gradient-success btn-md">ADD SUB TASK</a>
                      </div>
                      <table class="table table-hover table-striped text-nowrap" width="100%">
                        <tr>
                          <th>Sr. No.</th>
                          <th>Task Title</th>
                          <th>Priority</th>
                          <th>Posted Date</th>
                          <th>Posted By</th>
                          <th>Assigned To</th>
                          <th>Status</th>
                          <th>Action</th>
                        </tr>
                        <tr style="background-color: #f5da88;">
                          <td>1</td>
                          <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
                          <td>Highest</td>
                          <td>31/08/2020</td>
                          <td>Dharmesh</td>
                          <td>Jalpesh Gajjar</td>
                          <td>Inprogress</td>
                          <td>
                            <a href="viewTaskDetails.php" class="btn bg-gradient-primary"><i class="fa fa-eye" aria-hidden="true"></i></a>
                            <a href="editTaskDetails.php" class="btn bg-gradient-success"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
                            <a href="#" class="btn bg-gradient-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
                          </td>
                        </tr>
                        <tr style="background-color:#f9cdcd;">
                          <td>2</td>
                          <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
                          <td>Highest</td>
                          <td>31/08/2020</td>
                          <td>Dharmesh</td>
                          <td>Jalpesh Gajjar</td>
                          <td>Inprogress <br>With Delay</td>
                          <td>
                            <a href="viewTaskDetails.php" class="btn bg-gradient-primary"><i class="fa fa-eye" aria-hidden="true"></i></a>
                            <a href="editTaskDetails.php" class="btn bg-gradient-success"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
                            <a href="#" class="btn bg-gradient-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
                          </td>
                        </tr>
                        <tr style="background-color: #b8fac7;">
                          <td>3</td>
                          <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
                          <td>Highest</td>
                          <td>31/08/2020</td>
                          <td>Dharmesh</td>
                          <td>Jalpesh Gajjar</td>
                          <td>Completed <br>On/before time</td>
                          <td>
                            <a href="viewTaskDetails.php" class="btn bg-gradient-primary"><i class="fa fa-eye" aria-hidden="true"></i></a>
                            <a href="editTaskDetails.php" class="btn bg-gradient-success"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
                            <a href="#" class="btn bg-gradient-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
                          </td>
                        </tr>
                        <tr class="bg-danger">
                          <td>4</td>
                          <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
                          <td>Highest</td>
                          <td>31/08/2020</td>
                          <td>Dharmesh</td>
                          <td>Jalpesh Gajjar</td>
                          <td>Completed<br>With Delay</td>
                          <td>
                            <a href="viewTaskDetails.php" class="btn bg-gradient-primary"><i class="fa fa-eye" aria-hidden="true"></i></a>
                            <a href="editTaskDetails.php" class="btn bg-gradient-success"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
                            <a href="#" class="btn bg-gradient-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
                          </td>
                        </tr>
                        <tr>
                          <td>5</td>
                          <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
                          <td>Highest</td>
                          <td>31/08/2020</td>
                          <td>Dharmesh</td>
                          <td>Jalpesh Gajjar</td>
                          <td>New</td>
                          <td>
                            <a href="viewTaskDetails.php" class="btn bg-gradient-primary"><i class="fa fa-eye" aria-hidden="true"></i></a>
                            <a href="editTaskDetails.php" class="btn bg-gradient-success"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
                            <a href="#" class="btn bg-gradient-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
                          </td>
                        </tr>
                        <tr>
                          <td>6</td>
                          <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
                          <td>Highest</td>
                          <td>31/08/2020</td>
                          <td>Dharmesh</td>
                          <td>Jalpesh Gajjar</td>
                          <td>New</td>
                          <td>
                            <a href="viewTaskDetails.php" class="btn bg-gradient-primary"><i class="fa fa-eye" aria-hidden="true"></i></a>
                            <a href="editTaskDetails.php" class="btn bg-gradient-success"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
                            <a href="#" class="btn bg-gradient-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
                          </td>
                        </tr>
                        <tr>
                          <td>7</td>
                          <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
                          <td>Highest</td>
                          <td>31/08/2020</td>
                          <td>Dharmesh</td>
                          <td>Jalpesh Gajjar</td>
                          <td>New</td>
                          <td>
                            <a href="viewTaskDetails.php" class="btn bg-gradient-primary"><i class="fa fa-eye" aria-hidden="true"></i></a>
                            <a href="editTaskDetails.php" class="btn bg-gradient-success"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
                            <a href="#" class="btn bg-gradient-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
                          </td>
                        </tr>
                        <tr>
                          <td>8</td>
                          <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
                          <td>Highest</td>
                          <td>31/08/2020</td>
                          <td>Dharmesh</td>
                          <td>Jalpesh Gajjar</td>
                          <td>New</td>
                          <td>
                            <a href="viewTaskDetails.php" class="btn bg-gradient-primary"><i class="fa fa-eye" aria-hidden="true"></i></a>
                            <a href="editTaskDetails.php" class="btn bg-gradient-success"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
                            <a href="#" class="btn bg-gradient-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
                          </td>
                        </tr>
                        <tr>
                          <td>9</td>
                          <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
                          <td>Highest</td>
                          <td>31/08/2020</td>
                          <td>Dharmesh</td>
                          <td>Jalpesh Gajjar</td>
                          <td>New</td>
                          <td>
                            <a href="viewTaskDetails.php" class="btn bg-gradient-primary"><i class="fa fa-eye" aria-hidden="true"></i></a>
                            <a href="editTaskDetails.php" class="btn bg-gradient-success"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
                            <a href="#" class="btn bg-gradient-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
                          </td>
                        </tr>
                        <tr>
                          <td>10</td>
                          <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
                          <td>Highest</td>
                          <td>31/08/2020</td>
                          <td>Dharmesh</td>
                          <td>Jalpesh Gajjar</td>
                          <td>New</td>
                          <td>
                            <a href="viewTaskDetails.php" class="btn bg-gradient-primary"><i class="fa fa-eye" aria-hidden="true"></i></a>
                            <a href="editTaskDetails.php" class="btn bg-gradient-success"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>
                            <a href="#" class="btn bg-gradient-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
                          </td>
                        </tr>
                      </table>

                      
                    </div>
                   
                   
                  </div>
                
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
