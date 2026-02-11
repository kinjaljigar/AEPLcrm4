<!DOCTYPE html>
<html>
  <head>
     <title>Aashir Engineering | Add Contact</title>
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
                    <h3 class="card-title">ADD CONTACT</h3>
                    <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 150px;">
                      <div class="input-group-append">
                        <a href="contacts.php" class="btn btn-block bg-gradient-success btn-lg">CONTACT LIST</a>
                      </div>
                    </div>
                  </div>
                  </div>
                  <form role="form" id="distrobutorForm" method="POST">
                    <div class="card-body row">
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="contactname">Name</label>
                        <input type="text" name="contactname" class="form-control" id="contactname" placeholder="Enter Contact">
                      </div>
                     
                       <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="InputMobile">Mobile</label>
                        <input type="text" name="mobile" class="form-control" id="InputMobile" placeholder="Mobile">
                      </div>
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="InputEmail">Email</label>
                        <input type="text" name="email" class="form-control" id="InputEmail" placeholder="Email">
                      </div>  
                      
                    
                      
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="Designation">Designation</label>
                        <input type="text" name="Designation" class="form-control" id="Designation" placeholder="Designation">
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
