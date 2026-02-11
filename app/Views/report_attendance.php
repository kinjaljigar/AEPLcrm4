<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?php echo $view_data['page_title'] ?></h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-sm-12">
                        <?php if ($view_data['type'] == "daily") { ?>
                            <b>Date:</b> <input type="text" class="form-control inline date-picker" id="rpt_start"
                                name="rpt_start" value="<?php echo $view_data['rpt_start']; ?>"
                                style="display:inline;width:90px;" readonly>
                            <!-- <b>Range:</b> <input type="text" class="form-control inline date-picker" id="rpt_start" name="rpt_start" value="<?php echo $view_data['rpt_start']; ?>" style="display:inline;width:90px;" readonly> to <input type="text" class="form-control inline date-picker" id="rpt_end" name="rpt_end" value="<?php echo $view_data['rpt_end']; ?>" style="display:inline;width:90px;" readonly> -->
                        <?php } else { ?>
                            <b>Range:</b> <input type="text" class="form-control inline date-picker" id="rpt_start"
                                name="rpt_start" value="<?php echo $view_data['rpt_start']; ?>"
                                style="display:inline;width:90px;" readonly> to <input type="text"
                                class="form-control inline date-picker" id="rpt_end" name="rpt_end"
                                value="<?php echo $view_data['rpt_end']; ?>" style="display:inline;width:90px;" readonly>
                        <?php } ?>
                        <button type="button" class="btn btn-primary"
                            onclick="LoadFilter('<?php echo $view_data['type']; ?>')">Go</button>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">
                        Filter: <input class="form-control" name="txt_search" id="txt_search"
                            style="width:150px; display:inline" value="<?php echo $view_data['txt_search']; ?>" placeholder="Employee Name" />
                        <button type="button" id="main_add_button" onclick="LoadData();"
                            class="btn btn-primary margin">Show Employees</button>
                    </div>
                </div><br />
                <div class="row">
                    <div class="col-md-12">
                        <table id="datatable" class="table table-bordered table-hover responsive nowrap" width="100% ">
                            <thead>
                                <tr>
                                    <th>Employee Name</th>
                                    <th>Total Hours Worked</th>
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
<form action="<?php echo base_url("home/report_timesheet") ?>" method="post" id="rpt_form">
    <input type="hidden" name="rpt_start" id="rpt_start" value="" />
    <input type="hidden" name="rpt_end" id="rpt_end" value="" />
    <input type="hidden" name="txt_search" id="txt_search" value="" />
    <input type="hidden" name="type" id="type" value="<?php echo $view_data['type']; ?>" />
    <input type="hidden" name="u_id" id="u_id" value="" />
    <input type="hidden" name="u_name" id="u_name" value="" />
    <form>
        <script>
            var dataTable = null;
            var rpt_start = '<?php echo $view_data['rpt_start']; ?>';
            var rpt_end = '<?php echo $view_data['rpt_end']; ?>';

            function document_ready() {
                <?php if ($view_data['type'] == "daily") { ?>
                    //setDatePicker(".date-picker", { });
                    loadDateRange("#rpt_start", "#rpt_end");
                <?php } else { ?>
                    loadDateRange("#rpt_start", "#rpt_end");
                <?php } ?>
                LoadData();
            }

            function LoadFilter(rtype) {
                rpt_start = $("#rpt_start").val();
                if (rtype != "daily")
                    rpt_end = $("#rpt_end").val();
                LoadData();
            }

            function LoadData() {
                var dtConf = {
                    "ajax": {
                        url: "<?php echo base_url('api/reports'); ?>",
                        method: "post",
                        data: {
                            "type": "attendence",
                            "txt_search": $("#txt_search").val(),
                            "rpt_start": rpt_start,
                            "rpt_end": rpt_end,
                            "sub_type": '<?php echo $view_data['type']; ?>',
                        }
                    },
                    // "bPaginate": false,
                    // "bInfo": false,
                    pageLength: -1,
                    stripeClasses: ['r0', 'r1'],
                    bSort: false,
                    dom: 'Blfrtip',
                    "buttons": true,
                    //"searching": true,
                    "columnDefs": [

                        {
                            "targets": [0, 1],
                            "searchable": false,
                            'bSortable': false,
                            "orderable": false,
                        }
                    ],
                    "oLanguage": {
                        "sEmptyTable": "There is not any <b>Time Record</b> for your criteria.",
                    },
                };
                if (dataTable != null) {
                    dataTable.destroy();
                }
                dataTable = loadDataTable('#datatable', dtConf);



            }

            function postFormData(u_id, u_name) {
                $("#rpt_form > #rpt_start").val(rpt_start);
                $("#rpt_form > #rpt_end").val(rpt_end);
                $("#rpt_form > #txt_search").val($("#txt_search").val());
                $("#u_id").val(u_id);
                $("#u_name").val(u_name);
                $("#rpt_form").submit();
            }
        </script>