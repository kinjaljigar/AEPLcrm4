<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Dependency</h1>
    </section>
    <section class="content">
        <div class="box-group" id="accordion">
            <?php foreach ($view_data['projects'] as $project) { ?>
            <div class="panel box box-primary">
                <div class="box-header with-border" id="dataTableH<?php echo $project['p_id'] ?>"
                    onclick="Load<?php echo $project['p_id'] ?>()">
                    <h4 class="box-title">
                        <a data-toggle="collapse" data-parent="#accordion"
                            href="#collapseOne<?php echo $project['p_id'] ?>" aria-expanded="false" class="collapsed">
                            <?php echo $project['p_name'] ?> <span
                                class="pull-right hide"><?php echo $project['p_created'] ?></span>
                        </a>
                    </h4>
                </div>
                <div id="collapseOne<?php echo $project['p_id'] ?>" class="panel-collapse collapse"
                    aria-expanded="false" style="height: 0px;">
                    <div class="box-body dependancy_parent" id="dataTable<?php echo $project['p_id'] ?>">
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </section>

</div><!-- /.content-wrapper -->

<script>
<?php foreach ($view_data['projects'] as $project) { ?>

function Load<?php echo $project['p_id'] ?>() {
    if (!$("#dataTableH<?php echo $project['p_id'] ?>").hasClass('done')) {
        doAjax('api/reports', 'POST', {
            type: "dependency",
            p_id: <?php echo $project['p_id'] ?>
        }, function(res) {
            if (res.status == 'pass') {
                $.each(res.data, function(key, value) {
                    $("#dataTable<?php echo $project['p_id'] ?>").append('<div class="data">' + value
                        .t_dependancy + '</div><div class="border">&nbsp;</div>');
                });
            } else {
                showModal('ok', res.message, 'Error', 'modal-danger', 'modal-sm');
            }
        });
        $("#dataTableH<?php echo $project['p_id'] ?>").addClass('done');
    }
}
<?php } ?>
</script>