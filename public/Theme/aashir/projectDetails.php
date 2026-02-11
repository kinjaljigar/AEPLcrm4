<!DOCTYPE html>
<html>
  <head>
    <title>Aashir Engineering | Reliance Industries</title>
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
                    <h3 class="card-title">PROJECT: RELIANCE INDUSTRIES</h3>
                    <div class="card-tools">
                      <div class="input-group input-group-sm" style="width: 150px;">
                        <div class="input-group-append">
                          <a href="projects.php" class="btn btn-block bg-gradient-success btn-lg">PROJECTS LIST</a>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="row mt-4">
                    <div class="col-lg-2 col-md-4 col-sm-12 pl-3 pb-3">
                        <img src="/aashir/dist/img/ril-jamnagar.jpg" style="width: 100%;max-width: 200px;height: auto;">
                    </div>
                    <div class="col-lg-10 col-md-8 col-sm-12">
                        <p>
                            <b>Project Name: </b> Reliance Industries<br>
                            <b>Address: </b>101, Shivalik, Satellite, Ahmedabad-380015, Gujarat | Email: veda@gmail.com | Phone: 89832398328<br>
                            <b>Call:</b> 9879878887 | <b>Email: </b> ramesh@gmail.com<br>
                        </p>
                        <p><b>Project Details: </b><br>Donec at orci sem. Donec vitae ligula ut tortor pretium tincidunt vitae vitae lorem. Nam id ante id diam tempor convallis. Suspendisse porta erat leo, nec vulputate urna sollicitudin vitae. Pellentesque quis sapien quis lacus rutrum finibus. Maecenas scelerisque faucibus sapien eget imperdiet. Integer ut consectetur tellus, eu pellentesque nunc. Suspendisse ut ligula vel nisi mollis placerat placerat vel orci. Vestibulum nec justo sagittis, pulvinar urna ac, ullamcorper risus. Nunc ac elit ac diam pharetra sagittis quis eget tortor. Quisque velit mauris, finibus sit amet libero nec, consectetur pellentesque dolor. Praesent id pharetra dui</p>
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
                      <a class="nav-item nav-link  active" id="nav-tasks-tab" data-toggle="tab" href="#nav-tasks" role="tab" aria-controls="nav-tasks" aria-selected="false">Tasks</a>

                      <a class="nav-item nav-link" id="nav-emp-tab" data-toggle="tab" href="#nav-emp" role="tab" aria-controls="nav-emp" aria-selected="true">Team</a>
                      
                      <a class="nav-item nav-link" id="nav-account-tab" data-toggle="tab" href="#nav-account" role="tab" aria-controls="nav-account" aria-selected="false">Account</a>
                      <a class="nav-item nav-link" id="nav-files-tab" data-toggle="tab" href="#nav-files" role="tab" aria-controls="nav-files" aria-selected="false">Verbal Communication</a>
                    </div>
                  </nav>
                  <div class="tab-content py-3 px-3 px-sm-0" id="nav-tabContent">
                    <div class="tab-pane fade" id="nav-emp" role="tabpanel" aria-labelledby="nav-home-tab">
                      <table class="table table-hover table-striped text-nowrap" width="100%">
                        <tr>
                          <th></th>
                          <th>Employee Name</th>
                          <th>Email</th>
                          <th>Salary/hr</th>
                          <th>Hours Worked</th>
                          <th>Total Salary</th>
                        </tr>
                        <tr>
                          <td><input type="checkbox" name="empid"></td>
                          <td>Raj Malhotra</td>
                          <td>raj@aashir.com</td>
                          <td>500</td>
                          <td>50</td>
                          <td>25000</td>
                        </tr>
                        <tr>
                          <td><input type="checkbox" name="empid"></td>
                          <td>Jalpesh Gajjar</td>
                          <td>jalpesh@aashir.com</td>
                          <td>700</td>
                          <td>50</td>
                          <td>35000</td>
                        </tr>
                        <tr>
                          <td><input type="checkbox" name="empid"></td>
                          <td>Ravi Patel</td>
                          <td>ravi@aashir.com</td>
                          <td>600</td>
                          <td>50</td>
                          <td>30000</td>
                        </tr>
                        <tr>
                          <td><input type="checkbox" name="empid"></td>
                          <td>Nikunj Shah</td>
                          <td>nikunj@aashir.com</td>
                          <td>1000</td>
                          <td>50</td>
                          <td>50000</td>
                        </tr>
                        <tr>
                          <td colspan="5" class="text-right"><b>Total Salary: </b></td>
                          <td>140000</td>
                        </tr>
                        <tr><td colspan="6">
                          <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">SEND EMAIL</button>
                        </td></tr>
                      </table>
                    </div>
                    <div class="tab-pane fade show active" id="nav-tasks" role="tabpanel" aria-labelledby="nav-tasks-tab">
                      <div style="width: auto;float: right;margin-bottom: 10px;">
                        <a href="addTask.php" class="btn bg-gradient-success btn-md">ADD TASK</a>
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
                    <div class="tab-pane fade" id="nav-account" role="tabpanel" aria-labelledby="nav-account-tab">
                      <table class="table table-hover table-striped text-nowrap" style="width:50%" align="center">
                        <tr class="bg-dark text-light">
                          <th></th>
                          <th>Cost</th>
                        </tr>
                        <tr><td>Project Value</td><td><b>2,00,00,000</b></td></tr>
                        <tr><td>Expense 1</td><td>10,00,000</td></tr>
                        <tr><td>Expense 2</td><td>15,00,000</td></tr>
                        <tr><td>Expense 3</td><td>5,00,000</td></tr>
                        <tr><td>Expense 4</td><td>5,00,000</td></tr>
                        <tr><td>Expense 5</td><td>5,00,000</td></tr>
                        <tr><td>Salary</td><td>20,00,000</td></tr>
                        <tr class="bg-dark text-light"><td><b>Net Profit/Loss</b></td><td><b>1,40,00,000</b></td></tr>
                      </table>
                    </div>
                    <div class="tab-pane fade" id="nav-files" role="tabpanel" aria-labelledby="nav-files-tab">
                        <div class="row">
                          <div class="col-lg-12 col-md-12 col-sm-12" style="margin:0 auto;">
                          
                            <div class="row">
                              <div class="col-12">
                                <div class="p-2 mb-2 bg-info text-white">Verbal Communication </div>
                                <form role="form" id="distrobutorForm" method="POST">
                                  <div class="card-body row">
                                    <div class="form-group col-12">
                                      <label for="comments">Comments</label>
                                      <textarea name="comments" class="form-control" id="comments" placeholder="Comments" rows="5"></textarea>
                                    </div>

                                  </div>
                                  <div class="card-footer">
                                    <button type="submit" class="btn bg-gradient-info">Submit</button>
                                  </div>
                                </form>
                                <hr>
                                <div class="row">
                                  <div class="col-lg-12 mt-3"> 
                                    <table class="table table-hover table-striped" width="100%">
                                      <tr>
                                        <th><b>Date</b></th>                          
                                        <th><b>Comment</b></th>
                                       <th class="text-nowrap"><b>Posted By</b></th>
                                      </tr>
                                      <tr>
                                        <td>28/08/2020</td>
                                        <td>Donec at orci sem. Donec vitae ligula ut tortor pretium tincidunt vitae vitae lorem. Nam id ante id diam tempor convallis. Suspendisse porta erat leo, nec vulputate urna sollicitudin vitae. Pellentesque quis sapien quis lacus rutrum finibus.</td>
                                        <td class="text-nowrap">Jalpesh Gajjar</td>
                                      </tr>
                                    </table>
                                    
                                  </div>
                                </div>
                              </div>
                            </div>
                          
                        </div>
                        
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
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form>
      <div class="modal-body">
        
          <div class="form-group">
            <label for="emailsubject">Email Subject</label>
            <input type="emailsubject" class="form-control" id="emailsubject" aria-describedby="emailsubject" placeholder="Enter Subject">
            
          </div>
          <div class="form-group">
            <label for="emailbody">Message</label>
            <textarea name="emailbody" class="form-control" id="emailbody" placeholder="Enter your Message" rows="5"></textarea>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Submit</button>
      </div>
      </form>
    </div>
  </div>
</div>

    </body>
  </html>
