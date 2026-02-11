<!DOCTYPE html>
<html>
  <head>
     <title>Dr. Ila's Ayurved | Seasonal Diet</title>
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
                    <h3 class="card-title">ADD SEASONAL DIET: <b class="text-warning">WINTER</b></h3>
                    <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 150px;">
                      <div class="input-group-append">
                        <a href="seasonalDiet.php" class="btn btn-block bg-gradient-success btn-lg">SPECIAL DAY LIST</a>
                      </div>
                    </div>
                  </div>
                  </div>
                  <form role="form" id="distrobutorForm" method="POST">
                  

                  <div class="content">
                    <div class="container-fluid">
                      <div class="card card-default">
                        
                        <div class="card-body">
                          <div class="row">
                            <div class="form-group col-12">
                              <label for="seasonaldiet">Diet</label>
                              <textarea name="seasonaldiet" class="form-control" id="seasonaldiet" placeholder="Add Seasonal Diet" rows="12"></textarea>
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
