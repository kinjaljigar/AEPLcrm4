<div class="content-wrapper">
    <section class="content-header">
        <h1><?php echo $view_data['page_title'] ?></h1>
    </section>

    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-sm-12">
                        <b>Month:</b>
                        <select id="month" class="form-control inline" style="width:120px;">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= ($m == date('n')) ? 'selected' : '' ?>>
                                    <?= date("F", mktime(0, 0, 0, $m, 1)) ?>
                                </option>
                            <?php endfor; ?>
                        </select>

                        <b>Year:</b>
                        <select id="year" class="form-control inline" style="width:90px;">
                            <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                                <option value="<?= $y ?>" <?= ($y == date('Y')) ? 'selected' : '' ?>>
                                    <?= $y ?>
                                </option>
                            <?php endfor; ?>
                        </select>

                        <button type="button" class="btn btn-primary" onclick="LoadEmployeeAttendance()">Go</button>
                    </div>
                </div>
            </div>

            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">
                        Filter:
                        <input class="form-control" name="txt_search" id="txt_search"
                            style="width:200px; display:inline" placeholder="Employee Name" />
                        <button type="button" onclick="LoadEmployeeAttendance();"
                            class="btn btn-primary margin">Show Employees</button>
                    </div>
                </div><br />

                <div class="row">
                    <div class="col-md-12">
                        <table id="employee_attendance_table"
                            class="table table-bordered table-hover responsive nowrap" width="100%">
                            <thead>
                                <tr id="attendance_header">
                                    <th>#</th>
                                    <th>Employee Name</th>
                                    <!-- Days will be added dynamically -->
                                    <th>Total Hours</th>
                                </tr>
                            </thead>
                            <tbody class="admin_list"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    var dataTable = null;


    function document_ready() {
        LoadEmployeeAttendance();
    }


    // Generate DataTable columns dynamically based on number of days
    function generateColumns(days) {
        var cols = [{
                "data": 0
            }, // Serial
            {
                "data": 1
            } // Employee Name
        ];
        for (var i = 2; i < 2 + days; i++) {
            cols.push({
                "data": i
            });
        }
        cols.push({
            "data": 2 + days
        }); // Total Hours
        return cols;
    }

    function LoadEmployeeAttendance() {
        var month = $("#month").val();
        var year = $("#year").val();

        // Get days in month first
        $.ajax({
            url: "<?php echo base_url('api/reports'); ?>",
            type: "post",
            dataType: "json",
            data: {
                "type": "getDaysHeader",
                "month": month,
                "year": year
            },
            success: function(res) {
                var days = res.days;

                // Build header
                var headerHtml = "<th>#</th><th>Employee Name</th>";
                for (var d = 1; d <= days; d++) {
                    headerHtml += "<th>" + d + "</th>";
                }
                headerHtml += "<th>Total Hours</th>";
                $("#attendance_header").html(headerHtml);

                // Load DataTable
                var dtConf = {
                    "ajax": {
                        url: "<?php echo base_url('api/reports'); ?>",
                        method: "post",
                        data: {
                            "type": "attendencedaily",
                            "txt_search": $("#txt_search").val(),
                            "month": month,
                            "year": year,
                            "sub_type": "employee"
                        }
                    },
                    "columns": generateColumns(days),
                    pageLength: -1,
                    bSort: false,
                    dom: 'Blfrtip',
                    "buttons": [
                        'excelHtml5',
                        'csvHtml5',
                        {
                            extend: 'pdfHtml5',
                            orientation: 'landscape',
                            pageSize: 'A4',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33],
                                format: {
                                    body: function(data) {
                                        if (data === null || data === undefined) {
                                            return '';
                                        }
                                        return String(data).replace(/<[^>]*>/g, '');
                                    }
                                }
                            },
                            customize: function(doc) {
                                doc.defaultStyle.fontSize = 10;
                                doc.styles.tableHeader.fontSize = 11;
                                doc.pageMargins = [5, 5, 5, 5];

                                var table = doc.content[1].table;
                                var columnCount = table.body[0].length;

                                var widths = [];

                                for (var i = 0; i < columnCount; i++) {

                                    if (i === 0) {
                                        widths.push('3%');
                                    }
                                    
                                    else if (i === 1) {
                                        widths.push('15%');
                                    }
                                    else if (i === columnCount - 1) {
                                        widths.push('8%');
                                    }
                                    
                                    else {
                                        widths.push('2.3%');
                                    }
                                }

                                table.widths = widths;
                            }
                        },
                        'print'
                    ],

                    "oLanguage": {
                        "sEmptyTable": "No attendance data found for the selected criteria."
                    }
                };

                if (dataTable != null) {
                    dataTable.destroy();
                }
                dataTable = $('#employee_attendance_table').DataTable(dtConf);
            }
        });
    }

    // $(document).ready(function() {
    //     LoadEmployeeAttendance();
    // });
</script>