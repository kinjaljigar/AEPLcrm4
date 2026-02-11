<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Estimated Hours v/s Actual Hours</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <input class="form-control" name="txt_search" id="txt_search"
                            style="width:150px; display:inline" placeholder="Project Name" />
                        <select class="form-control" id="txt_p_status" name="txt_p_status"
                            style="width:auto; display:inline">
                            <option value="Active">Active</option>
                            <option value="Hold">Hold</option>
                            <option value="Completed">Completed</option>
                        </select> <button type="button" id="main_add_button" onclick="LoadData();"
                            class="btn btn-primary margin">Show Report</button>

                    </div>
                    <div class="col-md-5">
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <table id="datatable" class="table table-bordered table-hover responsive nowrap" width="100% ">
                            <thead>
                                <tr>
                                    <th>Project</th>
                                    <th>Estimated Hours</th>
                                    <th>Planned Hours</th>
                                    <th>Hours Worked</th>
                                    <th>&nbsp;</th>
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
                            <th>Project Name</th>
                            <th>Employee Name</th>
                            <th>Actual Hours</th>
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
var dataTable = null,
    datatablePop = null;

function document_ready() {
    LoadData();
}

function LoadData() {
    var dtConf = {
        "ajax": {
            url: "<?php echo base_url('api/reports'); ?>",
            method: "post",
            data: {
                "type": "estimated_actual",
                "txt_p_status": $("#txt_p_status").val(),
                "txt_search": $("#txt_search").val(),
            }
        },
        //"bPaginate": false,
        //"bInfo": false,
        pageLength: -1,
        stripeClasses: ['r0', 'r1'],
        bSort: false,
        dom: 'Blfrtip',
        "buttons": true,
        "columnDefs": [

            {
                "targets": [0, 1],
                "searchable": false,
                'bSortable': false,
                "orderable": false,
            }
        ],
        "oLanguage": {
            "sEmptyTable": "There is not any <b>Records</b> for your criteria.",
        },
    };
    if (dataTable != null) {
        dataTable.destroy();
    }
    dataTable = loadDataTable('#datatable', dtConf);
}

function showData(p_id, p_name) {
    var html = '<form class="formclass" id="admin_add_form" name="admin_add_form" enctype="multipart/form-data">';
    html += $('.admin_add_modal').html();
    html += '</form>';
    if (parseInt(p_id) > 0) {
        showModal('html', html, p_name, 'modal', 'modal-lg', function() {
            var dtConf = {
                "ajax": {
                    url: "<?php echo base_url('api/reports'); ?>",
                    method: "post",
                    data: {
                        "type": "estimated_actual_detail",
                        "p_id": p_id,
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
                    "targets": [0, 1, 2],
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
</script>