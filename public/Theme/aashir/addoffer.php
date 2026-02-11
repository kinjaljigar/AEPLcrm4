<!DOCTYPE html>
<html>
  <head>
     <title>NextView | Add Scheme</title>
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
                    <h3 class="card-title">ADD SCHEME</h3>
                    <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 150px;">
                      <div class="input-group-append">
                        <a href="distributors.php" class="btn btn-block bg-gradient-success btn-lg">SCHEME LIST</a>
                      </div>
                    </div>
                  </div>
                  </div>
                  <form role="form" id="distrobutorForm" method="POST">
                    
                    <div class="card-body row">
                      
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="InputOfferName">Scheme Name</label>
                        <input type="text" name="offername" class="form-control" id="InputOfferName" placeholder="Enter Scheme Title">
                      </div>
                      
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="InputOfferImage">Offer Image</label>
                        <div class="input-group">
                          <div class="custom-file">
                            <input type="file" class="custom-file-input" id="InputOfferImage">
                            <label class="custom-file-label" for="InputOfferImage">Choose Scheme Image</label>
                          </div>
                          
                        </div>
                      </div>
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="InputStartDate">Start Date</label>
                        <input type="text" name="startdate" class="form-control" id="InputStartDate" placeholder="Scheme Start Date">
                      </div>
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="InputEndDate">End Date</label>
                        <input type="text" name="enddate" class="form-control" id="InputEndDate" placeholder="Scheme End Date">
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-12">
                        <div class="card  card-info">
                          <div class="card-header">
                            <h3 class="card-title">Scheme Description</h3>
                             
                          </div>
                          <!-- /.card-header -->
                          <div class="card-body pad">
                            <div class="mb-3">
                              <textarea class="textarea" placeholder="Place some text here"
                                        style="width: 100%; height: 400px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;"></textarea>
                            </div>
                            
                          </div>
                        </div>
                      </div>
                      <!-- /.col-->
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
    <script src="plugins/summernote/summernote-bs4.min.js"></script>
  <script>
    $(function () {
      // Summernote
      $('.textarea').summernote()
    })
  </script>
  </body>
</html>
