<?php
$leaders = $view_data['leaders'];
$monday = $view_data['rpt_start'] ?? date('d-m-Y', strtotime('monday this week'));
$friday = $view_data['rpt_end'] ?? date('d-m-Y', strtotime('friday this week'));
$leader_id = $view_data['leader_id'];
?><div class="content-wrapper">
    <section class="content-header">
        <h1><?= $view_data['page_title'] ?></h1>
    </section>

    <section class="content">
        <div class="box box-sbpink">

            <!-- HEADER -->
            <div class="box-header">
                <div class="row">
                    <div class="col-sm-12">

                        <!-- Leader Selection -->
                        <b>Project Leader:</b>
                        <select id="leader_id" class="form-control"
                            style="width:220px; display:inline">
                            <option value="">-- Select Leader --</option>
                            <?php foreach ($leaders as $leader) { ?>
                                <option value="<?= $leader->u_id ?>" <?= ($leader->u_id == $leader_id) ? 'selected' : '' ?>>
                                    <?= $leader->u_name ?>
                                </option>
                            <?php } ?>
                        </select>

                        &nbsp;&nbsp;

                        <!-- Date Range -->
                        <b>Range:</b>
                        <input type="text" id="rpt_start" name="rpt_start" value="<?php print $monday; ?>" class="form-control inline" style="width:150px;">
                        <input type="text" id="rpt_end" name="rpt_end" value="<?php print $friday; ?>" class="form-control inline" style="width:150px;">

                        <button type="button" class="btn btn-primary"
                            onclick="LoadData()">Go</button>
                    </div>
                </div>
            </div>

            <!-- BODY -->
            <div class="box-body">

                <!-- Employee Search -->
                <div class="row">
                    <div class="col-xs-12">
                        <b>Employee Search:</b>
                        <input class="form-control"
                            name="txt_search"
                            id="txt_search"
                            style="width:200px; display:inline"
                            placeholder="Employee Name">

                        <button type="button"
                            onclick="LoadData()"
                            class="btn btn-primary margin">
                            Show Employees
                        </button>
                    </div>
                </div>

                <br />

                <!-- Table -->
                <div class="row">
                    <div class="col-md-12">
                        <table id="datatable"
                            class="table table-bordered table-hover responsive nowrap"
                            width="100%">
                            <thead>
                                <tr>
                                    <th>Employee Name</th>
                                    <th>Total Hours Worked</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>

<!-- Hidden form for View page -->
<form action="<?= base_url("home/report_timesheet") ?>" method="post" id="rpt_form">
    <input type="hidden" name="rpt_start" id="frm_rpt_start">
    <input type="hidden" name="rpt_end" id="frm_rpt_end">
    <input type="hidden" name="txt_search" id="frm_txt_search">
    <input type="hidden" name="leader_id" id="frm_leader_id">
    <input type="hidden" name="type" value="Leaderempattendance">
    <input type="hidden" name="u_id" id="u_id">
    <input type="hidden" name="u_name" id="u_name">
</form>

<script>
    var dataTable = null;
    var rpt_start = '';
    var rpt_end = '';


    function document_ready() {
        loadDateRange("#rpt_start", "#rpt_end");
        var leader_id = <?php echo ($leader_id != '') ? "'" . $leader_id . "'" : "' '"; ?>;
        if (leader_id != ' ') {
           LoadData();
        }

    }

    function LoadData() {

        var leader_id = $("#leader_id").val();

        if (leader_id === "") {
            alert("Please select a Project Leader");
            return;
        }

        rpt_start = $("#rpt_start").val();
        rpt_end = $("#rpt_end").val();
        var dtConf = {
            ajax: {
                url: "<?= base_url('api/reports'); ?>",
                method: "post",
                data: {
                    type: "Leaderempattendance",
                    leader_id: leader_id,
                    rpt_start: rpt_start,
                    rpt_end: rpt_end,
                    txt_search: $("#txt_search").val()
                }
            },
            pageLength: -1,
            bSort: false,
            destroy: true,
            dom: 'Blfrtip',
            columnDefs: [{
                targets: [0, 1, 2],
                orderable: false
            }],
            oLanguage: {
                sEmptyTable: "No employees found for selected leader."
            }
        };

        if (dataTable) {
            dataTable.destroy();
        }

        dataTable = loadDataTable('#datatable', dtConf);
    }

    function postFormData(u_id, u_name, $leader_id) {

        $("#frm_rpt_start").val(rpt_start);
        $("#frm_rpt_end").val(rpt_end);
        $("#frm_txt_search").val($("#txt_search").val());
        $("#u_id").val(u_id);
        $("#frm_leader_id").val($leader_id);
        $("#u_name").val(u_name);

        $("#rpt_form").submit();
    }
</script>