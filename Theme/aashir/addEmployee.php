<!DOCTYPE html>
<html>

<head>
  <title>Aashir Engineering | Add Doctor</title>
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
                  <h3 class="card-title">ADD EMPLOYEE</h3>
                  <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 150px;">
                      <div class="input-group-append">
                        <a href="employees.php" class="btn btn-block bg-gradient-success btn-lg">EMPLOYEE LIST</a>
                      </div>
                    </div>
                  </div>
                </div>
                <form role="form" id="distrobutorForm" method="POST">
                  <div class="card-body row">
                    <div class="form-group col-lg-4 col-md-6 col-sm-12">
                      <label for="username">Username</label>
                      <input type="text" name="username" class="form-control" id="username" placeholder="Enter username">
                    </div>
                    <div class="form-group col-lg-4 col-md-6 col-sm-12">
                      <label for="password1">Password</label>
                      <input type="password" name="password" class="form-control" id="password1" placeholder="Password">
                    </div>

                    <div class="form-group col-lg-4 col-md-6 col-sm-12">
                      <label for="employeename">Employee Name</label>
                      <input type="text" name="doctorname" class="form-control" id="doctorname" placeholder="Employee Name">
                    </div>

                    <div class="form-group col-lg-4 col-md-6 col-sm-12">
                      <label for="employeepic">Employee Picture</label>
                      <div class="input-group">
                        <div class="custom-file">
                          <input type="file" class="custom-file-input" id="employeepic">
                          <label class="custom-file-label" for="employeepic">Choose file</label>
                        </div>
                      </div>
                    </div>
                    <div class="form-group col-lg-4 col-md-6 col-sm-12">
                      <label for="joiningdate">Joining Date</label>
                      <input type="text" name="joiningdate" class="form-control" id="joiningdate" placeholder="Joining Date">
                    </div>

                    <div class="form-group col-lg-4 col-md-6 col-sm-12">
                      <label for="leavingdate">Leaving Date</label>
                      <input type="text" name="leavingdate" class="form-control" id="InputleavingdateMobile" placeholder="Leaving Date">
                    </div>


                    <div class="form-group col-lg-4 col-md-6 col-sm-12">
                      <label for="salary">Salary/hr</label>
                      <input type="text" name="salary" class="form-control" id="salary" placeholder="Salary">
                    </div>

                    <div class="form-group col-lg-4 col-md-6 col-sm-12">
                      <label for="InputMobile">Mobile</label>
                      <input type="text" name="mobile" class="form-control" id="InputMobile" placeholder="Mobile">
                    </div>
                    <div class="form-group col-lg-4 col-md-6 col-sm-12">
                      <label for="InputEmail">Email</label>
                      <input type="text" name="email" class="form-control" id="InputEmail" placeholder="Email">
                    </div>

                    <div class="form-group col-lg-4 col-md-6 col-sm-12">
                      <label for="address">Address</label>
                      <input type="text" name="address" class="form-control" id="address" placeholder="Address">
                    </div>

                    <div class="form-group col-lg-4 col-md-6 col-sm-12">
                      <label for="qualification">Qualification</label>
                      <input type="text" name="qualification" class="form-control" id="qualification" placeholder="Qualification">
                    </div>
                    <div class="form-group col-lg-4 col-md-6 col-sm-12">
                      <label for="department">Department</label>
                      <select class="form-control" name="department" id="department">
                        <option value="1">Architecture</option>
                        <option value="0">MEPF</option>
                        <option value="0">Admin</option>
                      </select>
                    </div>

                    <div class="form-group col-lg-4 col-md-6 col-sm-12">
                      <label for="status">Status</label>
                      <select class="form-control" name="status" id="status">
                        <option value="1">Active</option>
                        <option value="0">Deactive</option>
                      </select>
                    </div>
                    <div class="form-group col-lg-4 col-md-6 col-sm-12">
                      <label for="employeetype">Employee Type</label>
                      <select class="form-control" name="employeetype" id="employeetype">
                        <option value="1">Bim Head</option>
                        <option value="2">Project Leader</option>
                        <option value="3">Employee</option>
                      </select>
                    </div>
                    <div class="form-group col-lg-4 col-md-6 col-sm-12">
                      <label for="employeetype">Project Leader</label>
                      <select class="form-control" name="employeetype" id="employeetype">
                        <option value="1">Ishan Shah</option>
                        <option value="2">Tejas Sagar</option>
                        <option value="3">Jalpesh Gajjar</option>
                      </select>
                    </div>
                    <div class="form-group col-12">
                      <label for="doctorbio">Comments</label>
                      <textarea name="doctorbio" class="form-control" id="doctorbio" placeholder="Comments" rows="5"></textarea>
                    </div>
                  </div>
                  <div class="card-footer">
                    <button type="submit" class="btn bg-gradient-success">Submit</button>
                    <button type="button" class="btn bg-gradient-danger" onclick="window.history.back();">Cancel</button>
                  </div>
                </form>
              </div>
            </div>

            <div class="col-md-6">

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