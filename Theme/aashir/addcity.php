<!DOCTYPE html>
<html>
  <head>
     <title>NextView | Add City</title>
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
                    <h3 class="card-title">ADD CITY</h3>
                    <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 150px;">
                      <div class="input-group-append">
                        <a href="city.php" class="btn btn-block bg-gradient-success btn-lg">CITY LIST</a>
                      </div>
                    </div>
                  </div>
                  </div>
                  <form role="form" id="distrobutorForm" method="POST">
                    
                    <div class="card-body row">
                      
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
                    </div>
                    <div class="card-body row">
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="InputCityName">City Name</label>
                        <input type="text" name="offername" class="form-control" id="InputCityName" placeholder="Enter City Name">
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
