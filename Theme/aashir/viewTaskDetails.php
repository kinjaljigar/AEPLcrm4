<!DOCTYPE html>
<html>
  <head>
    <title>Aashir Engineering | Reliance Industries | Task</title>
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
                    <h3 class="card-title">RELIANCE INDUSTRIES: <span class="text-warning">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</span></h3>
                    <div class="card-tools">
                      
                      <div class="input-group input-group-sm" style="width: 150px;">
                        <div class="input-group-append">
                          <a href="projectDetails.php" class="btn btn-block bg-gradient-success btn-lg">BACK TO PROJECT</a>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="row mt-4">
                    <div class="col-lg-2 col-md-4 col-sm-12 pl-3 pb-3">
                        <img src="/aashir/dist/img/ril-jamnagar.jpg" style="width: 100%;max-width: 200px;height: auto;">
                    </div>
                    <div class="col-lg-10 col-md-8 col-sm-12">
                        <table class="table table-hover text-nowrap taskinfo" width="100%">
                          <tr>
                              <td><b>Created Date:</b></td>
                              <td>30/08/2020</td>
                              <td><b>Status:</b></td>
                              <td>InProgress</td>
                          
                              <td><b>Created By:</b></td>
                              <td>Dharmesh</td>
                              <td><b>Assigned To:</b></td>
                              <td>Jalpesh Gajjar</td>
                          </tr>
                          <tr>
                              <td><b>Estimated Hours:</b></td>
                              <td>50</td>
                              <td><b>Hours Worked:</b></td>
                              <td>30</td>
                              <td><b>Priority:</b></td>
                              <td>Highest</td>
                              <td><b>File Attached:</b></td>
                              <td><a href="#">abc.pdf</a></td>
                          </tr>
                        </table>
                        
                        <p><b>Task Details: </b><br>Donec at orci sem. Donec vitae ligula ut tortor pretium tincidunt vitae vitae lorem. Nam id ante id diam tempor convallis. Suspendisse porta erat leo, nec vulputate urna sollicitudin vitae. Pellentesque quis sapien quis lacus rutrum finibus. Maecenas scelerisque faucibus sapien eget imperdiet. Integer ut consectetur tellus, eu pellentesque nunc. Suspendisse ut ligula vel nisi mollis placerat placerat vel orci. Vestibulum nec justo sagittis, pulvinar urna ac, ullamcorper risus. Nunc ac elit ac diam pharetra sagittis quis eget tortor. Quisque velit mauris, finibus sit amet libero nec, consectetur pellentesque dolor. Praesent id pharetra dui</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section id="logmsgtabs" class="content">
          <div class="container-fluid">
            <div class="row">
              <div class="col-12 ">
                <nav>
                  <div class="nav nav-tabs nav-fill" id="nav-tab" role="tablist">
                    <a class="nav-item nav-link active" id="nav-log-tab" data-toggle="tab" href="#nav-log" role="tab" aria-controls="nav-log" aria-selected="true">LOG HOURS</a>
                    <a class="nav-item nav-link" id="nav-message-tab" data-toggle="tab" href="#nav-message" role="tab" aria-controls="nav-message" aria-selected="false">MESSAGE</a>
                  </div>
                </nav>
                <div class="tab-content py-3 px-3 px-sm-0" id="nav-tabContent">
                  <div class="tab-pane fade show active" id="nav-log" role="tabpanel" aria-labelledby="nav-log-tab">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-12">
                          <div class="p-2 mb-2 bg-info text-white">Log Hours</div>
                          <form>
                            
                            <div class="row">
                              <div class="col-10">
                                <div class="form-group">
                                  <label for="exampleFormControlTextarea1">Work Report</label>
                                  <textarea class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
                                </div>
                              </div>
                              <div class="col-2">
                                <div class="form-group" style="float: left;">
                                  <label for="exampleFormControlTextarea1">Hours</label>
                                  <select class="form-control" id="exampleFormControlSelect1">
                                    <option>1</option>
                                    <option>2</option>
                                    <option>3</option>
                                    <option>4</option>
                                    <option>5</option>
                                    <option>6</option>
                                    <option>7</option>
                                    <option>8</option>
                                  </select>
                                </div>
                              
                                <div class="form-group" style="float: left;margin-left: 10px;">
                                  <label for="exampleFormControlTextarea1">Minutes</label>
                                  <select class="form-control" id="exampleFormControlSelect1">
                                    <option>1</option>
                                    <option>2</option>
                                    <option>3</option>
                                    <option>4</option>
                                    <option>5</option>
                                    <option>6</option>
                                    <option>7</option>
                                    <option>8</option>
                                  </select>
                                </div>
                              </div>
                            </div>
                            <div class="row">
                              <div class="col-12 mb-3">
                                <button type="submit" class="btn btn-info">LOG HOURS</button>
                              </div>
                            </div>
                          </form>
                          <hr>
                          <div class="row">
                            <div class="col-lg-12 mt-3"> 
                              <table class="table table-hover table-striped" width="100%">
                                <tr>
                                  <th><b>Date</b></th>
                                  <th><b>Time</b></th>
                                  <th><b>Report</b></th>
                                  <th class="text-nowrap"><b>Posted By</b></th>
                                </tr>
                                <tr>
                                  <td>28/08/2020</td>
                                  <td>4</td>
                                  <td>Donec at orci sem. Donec vitae ligula ut tortor pretium tincidunt vitae vitae lorem. Nam id ante id diam tempor convallis. Suspendisse porta erat leo, nec vulputate urna sollicitudin vitae. Pellentesque quis sapien quis lacus rutrum finibus.</td>
                                  <td class="text-nowrap">Jalpesh Gajjar</td>
                                </tr>
                                <tr>
                                  <td>29/08/2020</td>
                                  <td>5:30</td>
                                  <td>Donec at orci sem. Donec vitae ligula ut tortor pretium tincidunt vitae vitae lorem. Nam id ante id diam tempor convallis. Suspendisse porta erat leo, nec vulputate urna sollicitudin vitae. Pellentesque quis sapien quis lacus rutrum finibus.</td>
                                  <td class="text-nowrap">Jalpesh Gajjar</td>
                                </tr>
                                <tr>
                                  <td>30/08/2020</td>
                                  <td>6</td>
                                  <td>Donec at orci sem. Donec vitae ligula ut tortor pretium tincidunt vitae vitae lorem. Nam id ante id diam tempor convallis. Suspendisse porta erat leo, nec vulputate urna sollicitudin vitae. Pellentesque quis sapien quis lacus rutrum finibus.</td>
                                  <td class="text-nowrap">Jalpesh Gajjar</td>
                                </tr>
                                <tr>
                                  <td>31/08/2020</td>
                                  <td>6:20</td>
                                  <td>Donec at orci sem. Donec vitae ligula ut tortor pretium tincidunt vitae vitae lorem. Nam id ante id diam tempor convallis. Suspendisse porta erat leo, nec vulputate urna sollicitudin vitae. Pellentesque quis sapien quis lacus rutrum finibus.</td>
                                  <td class="text-nowrap">Jalpesh Gajjar</td>
                                </tr>
                              </table>
                              <nav aria-label="...">
                                <ul class="pagination pagination-lg">
                                  <li class="page-item active" aria-current="page">
                                    <span class="page-link">
                                      1
                                      <span class="sr-only">(current)</span>
                                    </span>
                                  </li>
                                  <li class="page-item"><a class="page-link" href="#">2</a></li>
                                  <li class="page-item"><a class="page-link" href="#">3</a></li>
                                </ul>
                              </nav>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="tab-pane fade" id="nav-message" role="tabpanel" aria-labelledby="nav-message-tab">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-12">
                          <div class="p-2 mb-2 bg-info text-white">Make Comment </div>
                          <form role="form" id="distrobutorForm" method="POST">
                            <div class="card-body row">
                              <div class="form-group col-12">
                                <label for="comments">Comments</label>
                                <textarea name="comments" class="form-control" id="comments" placeholder="Comments" rows="5"></textarea>
                              </div>
                              <div class="form-group col-lg-6 col-md-6 col-sm-12">
                                <label for="projectpic">Attachment</label>
                                <div class="input-group">
                                  <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="projectpic">
                                    <label class="custom-file-label" for="projectpic">Choose file</label>
                                  </div>
                                </div>
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
                                  <th><b>Attachment</b></th>
                                  <th class="text-nowrap"><b>Posted By</b></th>
                                </tr>
                                <tr>
                                  <td>28/08/2020</td>
                                  <td>Donec at orci sem. Donec vitae ligula ut tortor pretium tincidunt vitae vitae lorem. Nam id ante id diam tempor convallis. Suspendisse porta erat leo, nec vulputate urna sollicitudin vitae. Pellentesque quis sapien quis lacus rutrum finibus.</td>
                                  <td><a href="#">abc.pdf</a></td>
                                  <td class="text-nowrap">Jalpesh Gajjar</td>
                                </tr>
                              </table>
                              <nav aria-label="...">
                                <ul class="pagination pagination-lg">
                                  <li class="page-item active" aria-current="page">
                                    <span class="page-link">
                                      1
                                      <span class="sr-only">(current)</span>
                                    </span>
                                  </li>
                                  <li class="page-item"><a class="page-link" href="#">2</a></li>
                                  <li class="page-item"><a class="page-link" href="#">3</a></li>
                                </ul>
                              </nav>
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



    </body>
  </html>
