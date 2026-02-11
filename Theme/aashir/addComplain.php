<!DOCTYPE html>
<html>
  <head>
     <title>NextView | Add Customer</title>
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
                    <h3 class="card-title">ADD COMPLAIN</h3>
                    <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 150px;">
                      <div class="input-group-append">
                        <a href="complains.php" class="btn btn-block bg-gradient-success btn-lg">COMPLAIN LIST</a>
                      </div>
                    </div>
                  </div>
                  </div>
                  <form role="form" id="complainForm" method="POST">
                    


                        <div class="card card-default">
                          
                          <div class="card-body">
                            <div class="row">
                              <div class="form-group col-lg-6 col-md-6 col-sm-12">
                                <label for="InputSerialNumber">Customer Name</label>
                                Pratik Shah
                              </div>

                              <div class="form-group col-lg-6 col-md-6 col-sm-12">
                                
                              </div>
                            </div>

                            <div class="row">
                              <div class="form-group col-lg-6 col-md-6 col-sm-12">
                                <label for="InputSerialNumber">Serial Number</label>
                                <input type="text" name="serialNumber" class="form-control" id="InputSerialNumber" placeholder="Serial Number">
                              </div>

                              <div class="form-group col-lg-6 col-md-6 col-sm-12">
                                <label for="InputModelNumber">Model Number</label>
                                <input type="text" name="modelNumber" class="form-control" id="InputModelNumber" placeholder="Model Number">
                              </div>
                            </div>

                            <div class="row">
                              <div class="form-group col-lg-6 col-md-6 col-sm-12">
                                <label for="InputSerice Center">Service Center</label>
                                <select class="form-control" name="serviceCenterID" id="InputServiceCenterId">
                                  <option value="1">Sales India</option>
                                  <option value="2">Reliance Digital</option>
                                  <option value="3">Croma</option>
                                  <option value="4">Vijay Sales</option>
                                  <option value="5">Ramesh Electronics</option>
                                </select>
                              </div>

                              <div class="form-group col-lg-6 col-md-6 col-sm-12">
                                <label for="InputComplainType">Complain Type</label>
                                <select class="form-control" name="complainType" id="InputComplainType">
                                  <option value="Installation">Installation</option>
                                  <option value="Complain">Complain</option>
                                </select>
                              </div>
                            </div>


                            <div class="row">
                              <div class="form-group col-lg-6 col-md-6 col-sm-12">
                                <label for="InputSerialNumber">Complain Title</label>
                                <input type="text" name="complainTitle" class="form-control" id="InputComplainTitle" placeholder="Complain Title">
                              </div>

                              <div class="form-group col-lg-6 col-md-6 col-sm-12">
                                <label for="InputInvoiceImage">Invoice Image</label>
                                <div class="input-group">
                                  <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="InputInvoiceImage">
                                    <label class="custom-file-label" for="InputInvoiceImage">Choose file</label>
                                  </div>
                                  
                                </div>
                              </div>
                            </div>

                            <div class="row">
                              <div class="form-group col-lg-12 col-md-12 col-sm-12">
                                <label for="InputComplainDetail">Complain Detail</label>
                                <textarea class="form-control" rows="3" placeholder="Enter Complain"></textarea>
                                <input type="hidden" name="complainStatus" value="New">
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
