<!DOCTYPE html>
<html>
  <head>
    <title>Aashir Engineering | Add Project</title>
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
                    <h3 class="card-title">ADD PROJECT</h3>
                    <div class="card-tools">
                      <div class="input-group input-group-sm" style="width: 150px;">
                        <div class="input-group-append">
                          <a href="projects.php" class="btn btn-block bg-gradient-success btn-lg">PROJECTS LIST</a>
                        </div>
                      </div>
                    </div>
                  </div>
                  <form role="form" id="distrobutorForm" method="POST">
                    <div class="card-body row">
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="projectnumber">Project Number</label>
                        <input type="text" name="projectnumber" class="form-control" id="projectnumber" placeholder="Enter Project Number">
                      </div>
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="projectname">Project Name</label>
                        <input type="text" name="projectname" class="form-control" id="projectname" placeholder="Enter Project Name">
                      </div>
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="projectpic">Project Picture</label>
                        <div class="input-group">
                          <div class="custom-file">
                            <input type="file" class="custom-file-input" id="projectpic">
                            <label class="custom-file-label" for="projectpic">Choose file</label>
                          </div>
                        </div>
                      </div>
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="projectvalue">Project Value</label>
                        <input type="text" name="projectvalue" class="form-control" id="projectvalue" placeholder="Project Value">
                      </div>
                      
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="contactnumber">Contact Number</label>
                        <input type="text" name="contactnumber" class="form-control" id="contactnumber" placeholder="Contact Number">
                      </div>
                      <div class="form-group col-lg-6 col-md-6 col-sm-12">
                        <label for="statusId">Project Status</label>
                        <select class="form-control" name="statusId" id="statusId">
                          <option value="1">New</option>
                          <option value="2">Active</option>
                          <option value="3">Hold</option>
                          <option value="4">Completed</option>
                        </select>
                      </div>
                      
                      <div class="form-group col-lg-12 col-md-12 col-sm-12">
                        <label for="projectaddress">Project Address</label>
                        <input type="text" name="projectaddress" class="form-control" id="projectaddress" placeholder="Project Address">
                      </div>
                      <div class="form-group col-12">
                        <label for="projectscope">Project Scope</label>
                        <textarea name="projectscope" class="form-control" id="projectscope" placeholder="Project Scope" rows="5"></textarea>
                      </div>
                      <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="dashboard">
                        <label class="form-check-label" for="dashboard">Show on dashboard</label>
                      </div>
                      
                    </div>
                    <div class="col-12">
                      <div class="card text-left">
                        <div class="card-header bg-primary"><h3 class="card-title">PROJECT EXPENSES </h3></div>
                        <div class="card-body">
                          <div class="row" >
                                <div class="col-5"><b>Expense Label</b></div>
                                <div class="col-5"><b>Expense Value</b></div>
                                <div class="col-2"></div>
                          </div>

                          <div  id="tblexpense">  
                              
                                <div class="row">
                                  <div class="col-5"><input type="text" name="expenselabel[]" class="form-control" id="expenselabel1" placeholder="Expense Label"></div>
                                  <div class="col-5"><input type="text" name="expensevalue[]" class="form-control" id="expensevalue1" placeholder="Expense Value"></div>
                                  <div class="col-2"></div>
                                </div>
                          </div>
                          <div class="row mt-4">
                            <div class="col-12">
                                  <button id="addprojectexpense" type="button" class="btn btn-info btn-flat btn-plus"><i class="fa fa-plus"></i></button>
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
      <script>
      $(document).ready(function() {
          var max_fields      = 10;
          var wrapper         = $("#tblexpense");
          var add_button      = $("#addprojectexpense");
        
          var x = 1;
          $(add_button).click(function(e){
              e.preventDefault();
              if(x < max_fields){
                  x++;
                  inputfields = '<div class="row"><div class="col-5 mt-2"><input type="text" name="expenselabel[]" class="form-control" id="expenselabel1" placeholder="Expense Label"></div><div class="col-5 mt-2"><input type="text" name="expensevalue[]" class="form-control" id="expensevalue1" placeholder="Expense Value"></div><div class="col-2 mt-2"><button type="button" class="delete btn btn-info btn-flat btn-remove"><i class="fa fa-minus"></i></button></div></div>'
                  $(wrapper).append(inputfields); //add input box
              }
        else
        {
        alert('You Reached the limits')
        }
          });
        
          $(wrapper).on("click",".delete", function(e){
              e.preventDefault(); 
              $(this).parent('div').parent('div').remove(); 
              x--;
          })
      });
      </script>
    </body>
  </html>
