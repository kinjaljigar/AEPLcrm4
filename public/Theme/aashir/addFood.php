<!DOCTYPE html>
<html>
  <head>
     <title>Dr. Ila's Ayurveda | Add Food</title>
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
                    <h3 class="card-title">ADD FOOD</h3>
                    <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 150px;">
                      <div class="input-group-append">
                        <a href="caloriechart.php" class="btn btn-block bg-gradient-success btn-lg">FOOD LIST</a>
                      </div>
                    </div>
                  </div>
                  </div>
                  <form role="form" id="distrobutorForm" method="POST">
                    <div class="card-body row">
                     

                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="foodname">Food Name</label>
                        <input type="text" name="foodname" class="form-control" id="foodname" placeholder="Food Name">
                      </div>
                      
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="calorie">Calorie</label>
                        <input type="text" name="calorie" class="form-control" id="calorie" placeholder="Calorie">
                      </div>
                      
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="fat">Fat</label>
                        <input type="text" name="fat" class="form-control" id="fat" placeholder="Fat">
                      </div>
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="protien">Protien</label>
                        <input type="text" name="protien" class="form-control" id="protien" placeholder="Protien">
                      </div>
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="crabs">Crabs</label>
                        <input type="text" name="crabs" class="form-control" id="crabs" placeholder="Crabs">
                      </div>
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="fiber">Fiber</label>
                        <input type="text" name="fiber" class="form-control" id="fiber" placeholder="Fiber">
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
