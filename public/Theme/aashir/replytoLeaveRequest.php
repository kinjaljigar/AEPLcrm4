<!DOCTYPE html>
<html>
  <head>
     <title>Aashir Engineering | Leave Request</title>
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
             <div class="card-tools">
              <div class="input-group input-group-sm" style="width: 150px;float:right;margin: 10px 0;">
                <div class="input-group-append">
                  <a href="leaveRequest.php" class="btn btn-block bg-gradient-success btn-lg">LEAVE REQUEST LIST</a>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-12">
                <div class="card card-primary">
                  <div class="card-header">
                    <div class="row">
                      <div class="col-lg-3 col-md-3 col-sm-12">
                        <h3 class="card-title">Jalpesh Gajjar</h3>
                      </div>
                      <div class="col-lg-9 col-md-9 col-sm-12">
                          <div class="row">
                            <div class="col-lg-8 col-md-6 col-sm-12">Need Leave for Marriage</div>
                            <div class="col-lg-4 col-md-6 col-sm-12"><h3 class="card-title">Request Date: 1/1/2020</h3></div>
                          </div>
                      </div>
                    </div>
                   </div>
                  </div>
                </div>
              </div>

            <div class="row">
              <div class="col-lg-4 col-md-4 col-sm-12">
                <p> 
                  <b>Jalpesh Gajjar</b><br>
                  <b>Mobile:</b> 9898989898<br>
                  <b>Email:</b> akpatel@gmail.com<br>
                </p>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-12">
                <p>
                  <b>Active Projects: </b>5<br>
                  <b>Onhand Tasks:</b> 25
                </p>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-12 text-center">
                <img src="/drila/dist/img/p1.jpg" height="100px">
              </div>
            </div>

            <div class="row mt-4">
              <div class="col-12  border-right">
                <div class="col-12">
                  <div class="text-center"><h4><a href="employeePerformance.php">View Peojects/Tasks Jalpesh Working on</a></h4></div>
                  <div class="bg-secondary text-white pl-2"><p><b>Leave Request:</b><br></p></div>
                  <div>
                    <p>
                      Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris congue libero vitae sapien varius pretium. Nulla et eleifend tellus, vel tincidunt justo. Nunc fringilla luctus facilisis. Mauris id leo ut ex congue pretium in vitae arcu. Cras vehicula ultricies nisi ullamcorper tincidunt. Cras id urna at augue sodales ultricies sed non odio. Nam sagittis lacus non augue porta sagittis. Donec ut metus ultrices, aliquet lorem a, ultricies tortor. Cras vestibulum, odio non ultricies interdum, urna elit finibus nisl, vel suscipit quam dui at nibh. Pellentesque tempor erat ac libero lacinia, quis semper nunc porta. Donec id tincidunt eros. Nullam maximus nunc id urna porttitor, a molestie diam rutrum. In purus mauris, pretium vel cursus non, interdum efficitur orci. In sed imperdiet leo. Etiam imperdiet tempor tortor at semper. Aliquam erat volutpat.
                   </p>
                </div>
                <div class="col-12 border bg-light text-dark">
                   <label for="InputComplainDetail">Reply</label>
                   <textarea class="form-control" rows="6" placeholder="Enter Reply"></textarea><br>
                    <input type="radio" name="status" value="1" checked=""> Approve
                    <input type="radio" name="status" value="0" class="ml-3"> Decline
                    <button type="submit" class="btn bg-gradient-success mt-3 ml-3 mb-2">Submit</button>
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
