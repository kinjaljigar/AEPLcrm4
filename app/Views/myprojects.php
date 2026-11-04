<div class="content-wrapper">
    <section class="content-header">
        <h1>My Projects</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <div class="row">
                    <div class="col-md-6">
                        <h3 class="box-title">Projects List</h3>
                    </div>
                    <div class="col-md-6">
                        <div class="pull-right" style="display:flex;align-items:center;gap:8px;">
                            <label style="margin:0;font-weight:normal;">Search:</label>
                            <input type="text" id="txt_project_search" class="form-control"
                                style="width:220px;" placeholder="Project number / name" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <?php if (empty($view_data['projects'])): ?>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> No projects assigned to you yet.
                    </div>
                <?php else: ?>
                <table class="table table-bordered table-hover" id="myprojects_table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Project No.</th>
                            <th>Project Name</th>
                            <th>Other Leaders</th>
                            <th>Status</th>
                            <th style="display:none;">APL</th>
                            <th style="display:none;">Team Members</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sr      = 1;
                        $apl_name = $view_data['apl_name'] ?? '';
                        foreach ($view_data['projects'] as $proj):
                            $pid     = $proj['p_id'];
                            $team    = $view_data['team_by_project'][$pid] ?? [];
                            $leaders = $view_data['leaders_by_project'][$pid] ?? [];
                            $statusClass = match($proj['p_status']) {
                                'Active'    => 'label-success',
                                'Hold'      => 'label-warning',
                                'Completed' => 'label-default',
                                default     => 'label-info',
                            };
                        ?>
                        <tr class="project-row">
                            <td><?= $sr++ ?></td>
                            <td><?= htmlspecialchars($proj['p_number'] ?? '') ?></td>
                            <td><?= htmlspecialchars($proj['p_name']) ?></td>
                            <td>
                                <?php if (!empty($leaders)): ?>
                                    <?php foreach ($leaders as $lname): ?>
                                        <span class="label label-primary" style="display:inline-block;margin:1px 2px;"><?= htmlspecialchars($lname) ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="label <?= $statusClass ?>">
                                    <?= htmlspecialchars($proj['p_status']) ?>
                                </span>
                            </td>
                            <td style="display:none;"></td>
                            <td style="display:none;"></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;">
                    <p class="text-muted" style="margin:0;">
                        Showing <strong id="range_from">1</strong>–<strong id="range_to">1</strong>
                        of <strong id="visible_count"><?= count($view_data['projects']) ?></strong> project(s)
                    </p>
                    <ul class="pagination pagination-sm" id="pagination_wrap" style="margin:0;"></ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<script>
    var PAGE_SIZE   = 20;
    var currentPage = 1;
    var allRows     = [];   // all <tr> elements after filtering

    function document_ready() {
        rebuildRows();

        $('#txt_project_search').on('keyup', function () {
            currentPage = 1;
            rebuildRows();
        });
    }

    // Collect filtered rows, render current page, render pagination
    function rebuildRows() {
        var val = $('#txt_project_search').val().toLowerCase().trim();
        allRows = [];

        $('#myprojects_table tbody tr.project-row').each(function () {
            var num  = $(this).find('td:eq(1)').text().toLowerCase();
            var name = $(this).find('td:eq(2)').text().toLowerCase();
            if (!val || num.indexOf(val) !== -1 || name.indexOf(val) !== -1) {
                allRows.push(this);
            }
        });

        $('#visible_count').text(allRows.length);
        renderPage(currentPage);
        renderPagination();
    }

    function renderPage(page) {
        currentPage = page;
        var start = (page - 1) * PAGE_SIZE;
        var end   = start + PAGE_SIZE;

        // Hide all rows first
        $('#myprojects_table tbody tr.project-row').hide();

        // Show only the rows for this page
        for (var i = start; i < Math.min(end, allRows.length); i++) {
            $(allRows[i]).show();
        }

        // Update range label
        if (allRows.length === 0) {
            $('#range_from').text(0);
            $('#range_to').text(0);
        } else {
            $('#range_from').text(start + 1);
            $('#range_to').text(Math.min(end, allRows.length));
        }

        renderPagination();
    }

    function renderPagination() {
        var totalPages = Math.ceil(allRows.length / PAGE_SIZE);
        var $wrap = $('#pagination_wrap');
        $wrap.empty();

        if (totalPages <= 1) return;

        // Prev
        $wrap.append(
            '<li class="' + (currentPage === 1 ? 'disabled' : '') + '">' +
            '<a href="javascript:;" onclick="if(' + currentPage + '>1) renderPage(' + (currentPage - 1) + ');">&laquo;</a></li>'
        );

        // Page numbers (show max 7 around current)
        var start = Math.max(1, currentPage - 3);
        var end   = Math.min(totalPages, start + 6);
        start     = Math.max(1, end - 6);

        if (start > 1) {
            $wrap.append('<li><a href="javascript:;" onclick="renderPage(1);">1</a></li>');
            if (start > 2) $wrap.append('<li class="disabled"><a>…</a></li>');
        }

        for (var p = start; p <= end; p++) {
            $wrap.append(
                '<li class="' + (p === currentPage ? 'active' : '') + '">' +
                '<a href="javascript:;" onclick="renderPage(' + p + ');">' + p + '</a></li>'
            );
        }

        if (end < totalPages) {
            if (end < totalPages - 1) $wrap.append('<li class="disabled"><a>…</a></li>');
            $wrap.append('<li><a href="javascript:;" onclick="renderPage(' + totalPages + ');">' + totalPages + '</a></li>');
        }

        // Next
        $wrap.append(
            '<li class="' + (currentPage === totalPages ? 'disabled' : '') + '">' +
            '<a href="javascript:;" onclick="if(' + currentPage + '<' + totalPages + ') renderPage(' + (currentPage + 1) + ');">&raquo;</a></li>'
        );
    }
</script>
