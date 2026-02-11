<!DOCTYPE html>
<html>
  <head>
     <title>Dr. Ila's Ayurved | General Diet</title>
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
                    <h3 class="card-title">ADD GENERAL DIET</h3>
                    <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 150px;">
                      <div class="input-group-append">
                        
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
                              <label for="breakfastdiet">Brakfast</label>
                              <textarea name="breakfastdiet" class="form-control" id="breakfastdiet" placeholder="Brakfast" rows="6"></textarea>
                            </div>
                            <div class="form-group col-12">
                              <label for="brunchdiet">Brunch</label>
                              <textarea name="brunchdiet" class="form-control" id="brunchdiet" placeholder="Brunch" rows="6"></textarea>
                            </div>
                            <div class="form-group col-12">
                              <label for="lunchdiet">Lunch</label>
                              <textarea name="lunchdiet" class="form-control" id="lunchdiet" placeholder="Lunch" rows="6"></textarea>
                            </div>
                            <div class="form-group col-12">
                              <label for="supperdiet">Supper</label>
                              <textarea name="supperdiet" class="form-control" id="supperdiet" placeholder="Supper" rows="6"></textarea>
                            </div>
                            <div class="form-group col-12">
                              <label for="dinnerdiet">Dinner</label>
                              <textarea name="dinnerdiet" class="form-control" id="dinnerdiet" placeholder="Dinner" rows="6"></textarea>
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
