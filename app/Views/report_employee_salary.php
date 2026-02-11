<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Employees Salary List</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Employees List</h3>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">
                        Filter: <input class="form-control" name="txt_search" id="txt_search"
                            style="width:150px; display:inline" placeholder="Employee Name" />
                        <select class="form-control" id="txt_U_Type" name="txt_U_Type"
                            style="width:auto; display:inline">
                            <option value="">Select User Type</option>
                            <option value="Employee">Employee</option>
                            <option value="Project Leader">Project Leader</option>
                            <option value="Bim Head">Bim Head</option>
                        </select>
                        <select class="form-control" id="txt_U_Status" name="txt_U_Status"
                            style="width:auto; display:inline">
                            <option value="">Select User Status</option>
                            <option value="Active">Active</option>
                            <option value="Deactive">Deactive</option>
                        </select>
                        <button type="button" id="main_add_button" onclick="LoadData();"
                            class="btn btn-primary margin">Show Employees</button>
                    </div>
                </div><br />
                <div class="row">
                    <div class="col-md-12">
                        <table id="datatable" class="table table-bordered table-hover responsive nowrap" width="100% ">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Employee Name</th>

                                    <th>Salary/Hr.</th>
                                    <th>User Type</th>
                                    <th width="120">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody class="admin_list">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="box-footer"></div>


        </div>

    </section>

</div><!-- /.content-wrapper -->
<div class="admin_add_modal" style="display: none;">
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <table id="datatablePop" class="table table-bordered table-hover responsive nowrap" width="100% ">
                    <thead>
                        <tr>
                            <th>Employee Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Salary</th>
                        </tr>
                    </thead>
                    <tbody class="admin_list">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
var STYPE = '';
var dataTable = null;
datatablePop = null;

function document_ready() {
    LoadData();
    doAjax('api/drop_get', 'POST', {
        dropobjs: [{
            'type': 'team_leader'
        }]
    }, function(res) {
        if (res.status == 'pass') {
            var record = res.data;
            $("#u_leader").html(record.team_leader);
        } else {
            showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
        }
    });

}

function LoadData() {
    var dtConf = {
        "ajax": {
            url: "<?php echo base_url('api/reports'); ?>",
            method: "post",
            data: {
                "type": "employee_salary_list",
                "txt_search": $("#txt_search").val(),
                "txt_U_Type": $("#txt_U_Type").val(),
                "txt_U_Status": $("#txt_U_Status").val(),
            }
        },
        "processing": true,
        serverSide: true,
        pageLength: -1,
        stripeClasses: ['r0', 'r1'],
        bSort: false,
        "columnDefs": [
            /*{
                           "targets": [0],
                           "searchable": false,
                           'bSortable': true,
                           "orderable": true,
                       }, */
            {
                "targets": [0, 1, 2, 3, 4],
                "searchable": false,
                'bSortable': false,
                "orderable": false,
            }
        ],
        "oLanguage": {
            "sEmptyTable": "There is not any <b>Employees</b> added with your criteria.",
        },
    };
    if (dataTable != null) {
        dataTable.destroy();
    }
    dataTable = loadDataTable('#datatable', dtConf);
}

function showData(u_id, u_name) {
    var html = '<form class="formclass" id="admin_add_form" name="admin_add_form" enctype="multipart/form-data">';
    html += $('.admin_add_modal').html();
    html += '</form>';
    if (parseInt(u_id) > 0) {
        showModal('html', html, u_name, 'modal', 'modal-lg', function() {
            var dtConf = {
                "ajax": {
                    url: "<?php echo base_url('api/reports'); ?>",
                    method: "post",
                    data: {
                        "type": "employee_salary_detail",
                        "u_id": u_id,
                    }
                },
                //"bPaginate": false,
                //"bInfo": false,
                pageLength: -1,
                stripeClasses: ['r0', 'r1'],
                bSort: false,
                dom: 'Blfrtip',
                "buttons": true,
                "columnDefs": [{
                    "targets": [0, 1, 2, 3],
                    "searchable": false,
                    'bSortable': false,
                    "orderable": false,
                }],
                "oLanguage": {
                    "sEmptyTable": "There is not any <b>Records</b> for your criteria.",
                },
            };
            if (datatablePop != null) {
                datatablePop.destroy();
            }
            datatablePop = loadDataTable('#admin_add_form #datatablePop', dtConf);
        });
    }
}

function KType() {
    return false;
    if ($("#admin_add_form input[name='KType']:checked").val() == "Registered") {
        $("#admin_add_form #ForRegRow").show();
    } else {
        $("#admin_add_form #ForRegRow").hide();
    }
}
</script>