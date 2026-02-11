<!DOCTYPE html>
<html>
  <head>
     <title>NextView | Add Product</title>
    <?php require("head.php"); ?>
    <link rel="stylesheet" href="plugins/summernote/summernote-bs4.css">
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
                    <h3 class="card-title">ADD PRODUCT</h3>
                    <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 150px;">
                      <div class="input-group-append">
                        <a href="products.php" class="btn btn-block bg-gradient-success btn-lg">PRODUCT LIST</a>
                      </div>
                    </div>
                  </div>
                  </div>
                  <form role="form" id="distrobutorForm" method="POST">
                    
                    <div class="row ml-3 mt-3">
                        <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="InputModel">Model #</label>
                        <input type="text" name="modelnumber" class="form-control" id="InputModel" placeholder="Enter Model #">
                      </div>
                    </div>
                    <div class="row ml-3">
                        <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="InputSerial">Serial #</label>
                        <input type="text" name="serialnumber" class="form-control" id="InputSerial" placeholder="Enter Serial #">
                      </div>
                    </div>
                    <div class="row ml-3">
                        <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="InputQuantity">Quantity</label>
                        <input type="text" name="quantity" class="form-control" id="InputQuantity" placeholder="Enter InputQuantity">
                      </div>
                    </div>

                     
                    <div class="card-footer">
                      <button type="submit" class="btn bg-gradient-success mr-3">Submit</button>
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
    <script src="plugins/summernote/summernote-bs4.min.js"></script>
  <script>
    $(function () {
      // Summernote
      $('.textarea').summernote()
    })
  </script>
  </body>
</html>
