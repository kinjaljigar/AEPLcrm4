<!DOCTYPE html>
<html>
  <head>
     <title>Dr. Ila's Ayurved | Add Patient</title>
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
                    <h3 class="card-title">ADD PATIENT</h3>
                    <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 150px;">
                      <div class="input-group-append">
                        <a href="patients.php" class="btn btn-block bg-gradient-success btn-lg">PATIENTS LIST</a>
                      </div>
                    </div>
                  </div>
                  </div>
                  <form role="form" id="distrobutorForm" method="POST">
                  <div class="container">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 col-sm-12">
                          <label for="packageid">Package</label>
                          <input type="text" name="startdate" class="form-control" id="startdate" placeholder="Weight Loss" disabled>
                      </div>
                      <div class="col-lg-4 col-md-4 col-sm-12">
                          <label for="startdae">Start Date</label>
                          <input type="text" name="startdate" class="form-control" id="startdate" placeholder="Start Date">
                      </div>
                      <div class="col-lg-4 col-md-4 col-sm-12">
                          <label for="enddate">End Date</label>
                          <input type="text" name="enddate" class="form-control" id="enddate" placeholder="End Date">
                      </div>
                    </div>
                  </div>
                  <div class="content mt-4">
                    <div class="container-fluid">
                      <div class="card card-default">
                        <div class="card-header bg-gradient-secondary text-white" >
                          <h3 class="card-title">PERSONAL INFO</h3>
                            <div class="card-tools">
                              <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>

                        <div class="card-body">
                          <div class="row">
                            <div class="form-group col-lg-6 col-md-6 col-sm-12">
                              <label for="username">Username</label>
                              <input type="text" name="username" class="form-control" id="username" placeholder="Username">
                            </div>
                            <div class="form-group col-lg-6 col-md-6 col-sm-12">
                              <label for="password">Password</label>
                              <input type="text" name="password" class="form-control" id="password" placeholder="Password">
                            </div>
                          </div>

                          <div class="row">
                            <div class="form-group col-lg-6 col-md-6 col-sm-12">
                              <label for="patientname">Patient Name</label>
                              <input type="text" name="patientname" class="form-control" id="patientname" placeholder="Patient Name">
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-12">
                              <label for="patientpic">Patient Picture</label>
                              <div class="input-group">
                                <div class="custom-file">
                                  <input type="file" class="custom-file-input" id="patientpic">
                                  <label class="custom-file-label" for="patientpic">Choose file</label>
                                </div>
                              </div>
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-12">
                              <label for="email">Email</label>
                              <input type="text" name="email" class="form-control" id="email" placeholder="Email">
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-12">
                              <label for="mobile">Mobile</label>
                              <input type="text" name="mobile" class="form-control" id="mobile" placeholder="Mobile">
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-12">
                              <label for="occupation">Occupation</label>
                              <input type="text" name="occupation" class="form-control" id="occupation" placeholder="Occupation">
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-12">
                              <label for="address">Address</label>
                              <input type="text" name="address" class="form-control" id="address" placeholder="Address">
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-12">
                              <label for="city">City</label>
                              <input type="text" name="city" class="form-control" id="city" placeholder="City">
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-12">
                              <label for="state">State</label>
                              <select class="form-control" name="stateid" id="stateid">
                                  <option value="0">Gujarat</option>
                                  <option value="1">Maharashtra</option>
                                  <option value="2">Rajsathan</option>
                                  <option value="3">Madhya Pradesh</option>
                              </select>
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-12">
                              <label for="birthdate">Birthdate <small>dd/mm/yyyy</small></label>
                              <input type="text" name="birthdate" class="form-control" id="birthdate" placeholder="Birthdate">
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-12">
                              <label for="city">Anniversary Date  <small>dd/mm/yyyy</small></label>
                              <input type="text" name="anniversarydate" class="form-control" id="anniversarydate" placeholder="Anniversary Date">
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>


                  <div class="content">
                    <div class="container-fluid">
                      <div class="card card-default">
                        <div class="card-header bg-gradient-secondary text-white" >
                          <h3 class="card-title">PATIENT'S MEASUREMENT</h3>
                            <div class="card-tools">
                              <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>

                        <div class="card-body">
                          <div class="row">
                            <div class="form-group col-lg-6 col-md-6 col-sm-12">
                              <label for="weight">Weight <small>(in kg)</small></label>
                              <input type="text" name="weight" class="form-control" id="weight" placeholder="Weight">
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-12">
                              <label for="height">Height</label>
                              <input type="text" name="height" class="form-control" id="height" placeholder="Height">
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-12">
                              <label for="waist">Waist</label>
                              <input type="text" name="waist" class="form-control" id="waist" placeholder="Waist">
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-12">
                              <label for="neck">Neck</label>
                              <input type="text" name="neck" class="form-control" id="neck" placeholder="Neck">
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-12">
                              <label for="chest">Chest</label>
                              <input type="text" name="chest" class="form-control" id="chest" placeholder="Chest">
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-12">
                              <label for="thigh">Thigh</label>
                              <input type="text" name="thigh" class="form-control" id="thigh" placeholder="Thigh">
                            </div>

                          </div>
                        </div>
                      </div>
                    </div>
                  </div>


                  <div class="content">
                    <div class="container-fluid">
                      <div class="card card-default">
                        <div class="card-header bg-gradient-secondary text-white" >
                          <h3 class="card-title">WAKEUP/SLEEPING & EATING TIME</h3>
                            <div class="card-tools">
                              <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>

                        <div class="card-body">
                          <div class="row">
                            <div class="form-group col-lg-6 col-md-6 col-sm-12">
                              <label for="wakeuptime">Wakeup Time</label>
                              <input type="text" name="wakeuptime" class="form-control" id="wakeuptime" placeholder="Wakeup Time">
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-12">
                              <label for="sleepingtime">Sleeping Time</label>
                              <input type="text" name="sleepingtime" class="form-control" id="sleepingtime" placeholder="Sleeping Time">
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-12">
                              <label for="breakfast">Breakfast Time</label>
                              <input type="text" name="breakfast" class="form-control" id="breakfast" placeholder="Breakfast Time">
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-12">
                              <label for="lunchtime">Lunch Time</label>
                              <input type="text" name="lunchtime" class="form-control" id="lunchtime" placeholder="Lunch Time">
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-12">
                              <label for="dinnertime">Dinner Time</label>
                              <input type="text" name="dinnertime" class="form-control" id="dinnertime" placeholder="Dinner Time">
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-12">
                              <label for="thigh">Thigh</label>
                              <input type="text" name="thigh" class="form-control" id="thigh" placeholder="Thigh">
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="content">
                    <div class="container-fluid">
                      <div class="card card-default">
                        <div class="card-header bg-gradient-secondary text-white" >
                          <h3 class="card-title">ADDITIONAL INFORMATION</h3>
                            <div class="card-tools">
                              <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>

                        <div class="card-body">
                          <div class="row">
                            <div class="form-group col-12">
                              <label for="medicalhistory">Medical History</label>
                              <textarea name="medicalhistory" class="form-control" id="medicalhistory" placeholder="Medical History" rows="8"></textarea>
                            </div>
                            <div class="form-group col-12">
                              <label for="doctorcomment">Doctor's Comments</label>
                              <textarea name="doctorcomment" class="form-control" id="doctorcomment" placeholder="Doctor's Comments" rows="8"></textarea>
                            </div>
                            <div class="form-group col-12">
                              <label for="generalinfo">General Info</label>
                              <textarea name="generalinfo" class="form-control" id="generalinfo" placeholder="General Information" rows="8"></textarea>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="card-footer">
                    <button type="submit" class="btn bg-gradient-success">Submit</button>
                    <button type="button" class="btn bg-gradient-danger" onclick="window.history.back();">Cancel</button>
                  </div>
                </form>
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
