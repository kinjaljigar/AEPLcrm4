<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Employee Present Departmentwise <?php ?></h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Employee Present Departmentwise</h3>
                    </div>

                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">

                    </div>
                </div><br />
                <?php if ($view_data['admin_session']['u_type'] == 'Master Admin' || $view_data['admin_session']['u_type'] == 'Bim Head') { ?>
                    <div class="box box-sbpink">
                        <div class="box-header">                           
                        </div>
                        <div class="box-body">
                            <table id="dataTablePresent" class="table table-bordered responsive nowrap" width="100%">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>EMail</th>
                                        <th>Mobile</th>
                                        <th>Type</th>
                                        <th>Department</th>
                                    </tr>
                                </thead>
                                <tbody class="admin_list">
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="box-footer"></div>


        </div>

    </section>

</div><!-- /.content-wrapper -->
<div class="admin_add_modal" style="display: none;">
    <div class="box-body">
        <input type="hidden" name="h_id" id="h_id">
        <div class="form-group">
            <label for="h_date">Date</label>
            <input type="text" class="form-control date-picker" id="h_date" name="h_date" value="" placeholder="">
        </div>
        <div class="form-group">
            <label for="h_title">Title</label>
            <input type="text" class="form-control" id="h_title" name="h_title" value="" placeholder="">
        </div>
    </div>
    <!-- /.box-body -->

    <div class="box-footer">
        <button type="button" class="btn btn-danger margin pull-right" data-dismiss="modal">Cancel</button>
        <button type="button" id="main_add_button" onclick="saveMain('C');" class="btn btn-primary margin pull-right">Save</button>
    </div>

</div>
<script>
    var STYPE = '';
    var dataTablePresent = null;

    function document_ready() {
        LoadData();
    }

    function LoadData() {
        var dtConf = {
            ajax: {
                url: "<?php echo base_url('api/dashboard'); ?>",
                method: "post",
                data: {
                    "type": "present_list",
                }
            },
            bPaginate: false,
            bInfo: false,
            pageLength: -1,
            stripeClasses: ['r0', 'r1'],
            bSort: false,
            columnDefs: [{
                "targets": [0, 1],
                "searchable": false,
                'bSortable': false,
                "orderable": false,
            }],
            oLanguage: {
                "sEmptyTable": "There is not any <b>Absent Today</b>.",
            },
        };
        if (dataTablePresent != null) {
            dataTablePresent.destroy();
        }
        dataTablePresent = loadDataTable('#dataTablePresent', dtConf);
    }
</script>