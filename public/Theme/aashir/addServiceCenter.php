<!DOCTYPE html>
<html>
  <head>
     <title>NextView | Add Service Center</title>
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
                    <h3 class="card-title">ADD SERVICE CENTER</h3>
                    <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 150px;">
                      <div class="input-group-append">
                        <a href="servicecenter.php" class="btn btn-block bg-gradient-success btn-lg">SERVICE CENTER LIST</a>
                      </div>
                    </div>
                  </div>
                  </div>
                  <form role="form" id="serviceCenterForm" method="POST">
                    <div class="card-body row">
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="InputUsername">Username</label>
                        <input type="text" name="username" class="form-control" id="InputUsername" placeholder="Enter username">
                      </div>
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="InputPassword1">Password</label>
                        <input type="password" name="password" class="form-control" id="InputPassword1" placeholder="Password">
                      </div>
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="InputCompanyName">Company Name</label>
                        <input type="text" name="companyname" class="form-control" id="InputCompanyName" placeholder="Company Name">
                      </div>
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="InputContactPerson">Contact Person</label>
                        <input type="text" name="contactperson" class="form-control" id="InputContactPerson" placeholder="Contact Person">
                      </div>
                      
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="InputZone">Zone</label>
                        <select class="form-control" name="zone" id="InputZone">
                          <option value="North Gujarat">North Gujarat</option>
                          <option value="South Gujarat">South Gujarat</option>
                          <option value="Central Gujarat">Central Gujarat</option>
                          <option value="Saurashtra">Saurashtra</option>
                          <option value="Kutch">Kutch</option>
                        </select>
                      </div>
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="InputCity">City</label>
                        <select class="form-control" name="city" id="InputCity">
                          <option value="1">Ahmedabad</option>
                          <option value="2">Surat</option>
                          <option value="3">Rajkot</option>
                          <option value="4">Vadodara</option>
                          <option value="5">Jamnagar</option>
                        </select>
                      </div>
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="InputVillage">Village</label>
                        <input type="text" name="distributorcode" class="form-control" id="InputVillage" placeholder="Village">
                      </div>
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="InputPincode">Pincode</label>
                        <input type="text" name="pincode" class="form-control" id="InputPincode" placeholder="Pincode">
                      </div>
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="InputMobile">Mobile</label>
                        <input type="text" name="mobile" class="form-control" id="InputMobile" placeholder="Mobile">
                      </div>
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="InputEmail">Email</label>
                        <input type="text" name="email" class="form-control" id="InputEmail" placeholder="Email">
                      </div>
                     
                    </div>
                    <div class="card card-secondary">
                      <div class="card-header">
                        <h3 class="card-title">ADD SERVICE ENGINEER</h3>
                      </div>
                      <div class="card-body">
                        <div class="row mb-2">
                          <div class="col-6"><input type="text" Name="enginnerName1" class="form-control" placeholder="Engineer Name"></div>
                          <div class="col-6"><input type="text" Name="enginnerMobile1"  class="form-control" placeholder="Engineer Mobile"></div>
                        </div>
                        <div class="row mb-2">
                          <div class="col-6"><input type="text" Name="enginnerName2" class="form-control" placeholder="Engineer Name"></div>
                          <div class="col-6"><input type="text" Name="enginnerMobile2"  class="form-control" placeholder="Engineer Mobile"></div>
                        </div>
                         <div class="row mb-2">
                          <div class="col-6"><input type="text" Name="enginnerName3" class="form-control" placeholder="Engineer Name"></div>
                          <div class="col-6"><input type="text" Name="enginnerMobile3"  class="form-control" placeholder="Engineer Mobile"></div>
                        </div>
                         <div class="row mb-2">
                          <div class="col-6"><input type="text" Name="enginnerName4" class="form-control" placeholder="Engineer Name"></div>
                          <div class="col-6"><input type="text" Name="enginnerMobile4"  class="form-control" placeholder="Engineer Mobile"></div>
                        </div>
                         <div class="row mb-2">
                          <div class="col-6"><input type="text" Name="enginnerName5" class="form-control" placeholder="Engineer Name"></div>
                          <div class="col-6"><input type="text" Name="enginnerMobile5"  class="form-control" placeholder="Engineer Mobile"></div>
                        </div>
                         <div class="row mb-2">
                          <div class="col-6"><input type="text" Name="enginnerName6" class="form-control" placeholder="Engineer Name"></div>
                          <div class="col-6"><input type="text" Name="enginnerMobile6"  class="form-control" placeholder="Engineer Mobile"></div>
                        </div>
                         <div class="row mb-2">
                          <div class="col-6"><input type="text" Name="enginnerName7" class="form-control" placeholder="Engineer Name"></div>
                          <div class="col-6"><input type="text" Name="enginnerMobile7"  class="form-control" placeholder="Engineer Mobile"></div>
                        </div>
                         <div class="row mb-2">
                          <div class="col-6"><input type="text" Name="enginnerName8" class="form-control" placeholder="Engineer Name"></div>
                          <div class="col-6"><input type="text" Name="enginnerMobile8"  class="form-control" placeholder="Engineer Mobile"></div>
                        </div>
                         <div class="row mb-2">
                          <div class="col-6"><input type="text" Name="enginnerName9" class="form-control" placeholder="Engineer Name"></div>
                          <div class="col-6"><input type="text" Name="enginnerMobile9"  class="form-control" placeholder="Engineer Mobile"></div>
                        </div>
                         <div class="row mb-2">
                          <div class="col-6"><input type="text" Name="enginnerName10" class="form-control" placeholder="Engineer Name"></div>
                          <div class="col-6"><input type="text" Name="enginnerMobile10"  class="form-control" placeholder="Engineer Mobile"></div>
                        </div>
                      </div>
                      <!-- /.card-body -->
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
