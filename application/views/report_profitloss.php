<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Profit/Loss Report</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">
                        Filter: <input class="form-control" name="txt_search" id="txt_search"
                            style="width:150px; display:inline" placeholder="Project No" />
                        <select class="form-control" id="txt_p_cat" name="txt_p_cat" style="width:auto; display:inline">
                            <?php
                            foreach ($view_data['p_cat'] as $val) {
                                echo '<option value="' . $val . '">' . $val . '</option>';
                            }
                            ?>
                        </select>
                        <select class="form-control" id="txt_p_status" name="txt_p_status"
                            style="width:auto; display:inline">
                            <option value="Active">Active</option>
                            <option value="Hold">Hold</option>
                            <option value="Completed">Completed</option>
                        </select> <button type="button" id="main_add_button" onclick="LoadData();"
                            class="btn btn-primary margin">Show Projects</button>
                    </div>
                </div><br />
                <div class="row">
                    <div class="col-md-12">
                        <table id="datatable" class="table table-bordered table-hover responsive nowrap" width="100% ">
                            <thead>
                                <tr>
                                    <th>Project Number</th>
                                    <th>Project Name</th>
                                    <th>Address</th>
                                    <?php if ($view_data['admin_session']['u_type'] == 'Master Admin') { ?>
                                        <th>Cost</th>
                                        <th>Expense</th>
                                        <th>Profit/Loss</th>
                                    <?php } ?>
                                    <th>Status</th>
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
                            <th>Project Number</th>
                            <th>Project Name</th>
                            <th>Employee Name</th>
                            <th>Total Hrs</th>
                            <?php if ($view_data['admin_session']['u_type'] == 'Master Admin') { ?>
                                <th>Total Salary</th>
                            <?php } ?>
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
    var datatablePop = null;

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
        //$("#btnSubmit").attr("disabled", true);
        var dtConf = {
            "ajax": {
                url: "<?php echo base_url('api/reports'); ?>",
                method: "post",
                data: {
                    "type": "projectprofitloss",
                    "txt_search": $("#txt_search").val(),
                    "txt_p_cat": $("#txt_p_cat").val(),
                    "txt_p_status": $("#txt_p_status").val(),
                }
            },
            pageLength: -1,
            stripeClasses: ['r0', 'r1'],
            bSort: false,
            dom: 'Blfrtip',
            "buttons": true,
            "columnDefs": [
                /*{
                               "targets": [0],
                               "searchable": false,
                               'bSortable': true,
                               "orderable": true,
                           }, */

            ],
            "oLanguage": {
                "sEmptyTable": "There is not any <b>Projects</b> added with your criteria.",
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
                            "type": "pemployeedetail",
                            "p_id": p_id,

                        }
                    },
                    "processing": true,
                    serverSide: true,
                    //"bPaginate": false,
                    // "bInfo": false,
                    pageLength: -1,
                    stripeClasses: ['r0', 'r1'],
                    bSort: false,
                    dom: 'Blfrtip',
                    "buttons": true,
                    "columnDefs": [{
                        // "targets": [0, 1, 2, 3],
                        // "searchable": false,
                        // 'bSortable': false,
                        // "orderable": false,
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