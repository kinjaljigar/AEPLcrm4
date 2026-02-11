<!DOCTYPE html>
<html>
  <head>
    <title>Aashir Engineering | Add Task</title>
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
                  <h3 class="card-title">ADD TASK FOR <span class="text-warning">RELIANCE INDUSTRY</span></h3>
                  <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 150px;">
                      <div class="input-group-append">
                        <a href="projectDetails.php" class="btn btn-block bg-gradient-success btn-lg">TASKS LIST</a>
                      </div>
                    </div>
                  </div>
                </div>
                <form role="form" id="distrobutorForm" method="POST">
                  <div class="card-body row">
                    <div class="form-group col-lg-8 col-md-6 col-sm-12">
                      <label for="tasktitle">Task Title</label>
                      <input type="text" name="tasktitle" class="form-control" id="tasktitle" placeholder="Enter Task Title">
                    </div>

                    <div class="form-group col-lg-2 col-md-4 col-sm-12">
                      <label for="taskpriority">Priority</label>
                      <select class="form-control" name="taskpriority" id="taskpriority">
                        <option value="1">Low</option>
                        <option value="2">Medium</option>
                        <option value="3">High</option>
                        <option value="4">Highest</option>
                      </select>
                    </div>
                    <div class="form-group col-lg-2 col-md-4 col-sm-12">
                      <label for="estimatedhours">Estimated Hours</label>
                      <select class="form-control" name="esthr" id="esthr" >
                      <?php for($i=1;$i<201;$i++): ?>
                      <option value="<?=$i;?>"><?=$i?></option>
                      <?php endfor; ?>
                      </select>
                    </div>

                    <div class="form-group col-12">
                      <label for="taskdesc">Task Description</label>
                      <textarea name="taskdesc" class="form-control" id="taskdesc" placeholder="Task Description" rows="5"></textarea>
                    </div>

                    <div class="form-group col-12">
                      <label for="taskdependency">Task Dependency</label>
                      <textarea name="taskdependency" class="form-control" id="taskdependency" placeholder="Task Dependency" rows="5"></textarea>
                    </div>

                    <div class="form-group col-12">
                      <table class="table table-hover table-striped table-bordered " width="100%">
                        <tr>
                          <th></th>
                          <th>Employee Name</th>
                          <th>Active Projects</th>
                          <th>On Hand Tasks</th>
                          <th>On Leave</th>
                        </tr>
                        <tr>
                          <td class="text-center"><input type="checkbox" name="empId" value="1"></td>
                          <td>Jalpesh Gajjar</td>
                          <td>
                              <a data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">10 </a>
                              <div class="collapse" id="collapseExample">
                                <div class="card card-body">
                                  <ul style="padding: 0 0 0 5px;">
                                    <li>Project 1 Title goes here</li>
                                    <li>Project 2</li>
                                    <li>Project 3</li>
                                  </ul>
                                </div>
                              </div>
                          </td>
                          <td>25</td>
                          <td>From: 15/09/2020 To: 20/09/2020</td>
                        </tr>
                        <tr>
                          <td class="text-center"><input type="checkbox" name="empId" value="1"></td>
                          <td>Jinal Patel</td>
                          <td><a href="employeeProjects.php">12</a></td>
                          <td>35</td>
                          <td>From: 12/09/2020 To: 18/09/2020</td>
                        </tr>
                        <tr>
                          <td class="text-center"><input type="checkbox" name="empId" value="1"></td>
                          <td>Sujit Shah</td>
                          <td><a href="employeeProjects.php">5</a></td>
                          <td>10</td>
                          <td>-</td>
                        </tr>
                        <tr>
                          <td class="text-center"><input type="checkbox" name="empId" value="1"></td>
                          <td>Disha Oza</td>
                          <td><a href="employeeProjects.php">2</a></td>
                          <td>12</td>
                          <td>-</td>
                        </tr>
                      </table>
                    </div>
                    <div class="col-12">
                      <div class="row" >
                        <div class="col-5"><b>File Label</b></div>
                        <div class="col-5"><b>Attach File</b></div>
                        <div class="col-2"></div>
                      </div>

                      <div  id="tblFiles">  
                        <div class="row">
                          <div class="col-5">
                            <input type="text" name="filelables[]" class="form-control" id="expenselabel1" placeholder="File Label">
                          </div>
                          <div class="form-group col-5">
                            <div class="input-group">
                              <div class="custom-file">
                                <input type="file" class="custom-file-input" id="filename">
                                <label class="custom-file-label" for="projectpic">Choose file</label>
                              </div>
                            </div>
                          </div>
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
                  <div class="card-footer">
                      <button type="submit" class="btn bg-gradient-success">Submit</button>
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
    var wrapper         = $("#tblFiles");
    var add_button      = $("#addprojectexpense");

    var x = 1;
    $(add_button).click(function(e)
    {
      e.preventDefault();
      if(x < max_fields)
      {
        x++;
        inputfields = '<div class="row"><div class="col-5 mt-2"><input type="text" name="expenselabel[]" class="form-control" id="expenselabel1" placeholder="Expense Label"></div><div class="form-group col-5"><div class="input-group"><div class="custom-file"><input type="file" class="custom-file-input" id="filename"><label class="custom-file-label" for="projectpic">Choose file</label></div></div></div><div class="col-2 mt-2"><button type="button" class="delete btn btn-info btn-flat btn-remove"><i class="fa fa-minus"></i></button></div></div>'
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
