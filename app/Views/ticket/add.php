<?php
$token = isset($view_data['token']) ? $view_data['token'] : '';
$categories = $view_data['categories'];
?>

<h1>Ticket</h1>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Add Ticket</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Add Ticket</h3>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <form method="post" action="<?= base_url('ticket/store') ?>" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Category:</label>
                        <!-- <select name="category_id">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat->id ?>">
                                <?= str_repeat('— ', isset($cat->level) ? $cat->level : 0) . htmlspecialchars($cat->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select><br><br> -->

                        <select name="parent_category" id="parent_category" class="form-control" required>
                            <option value="">Select Parent Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">

                        <label>Sub Category:</label>
                        <select name="category_id" id="child_category" class="form-control" required>
                            <option value="">-- No Subcategories --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Desktop Number</label>
                        <input type="text" name="desktop_number" value="" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subject" value="" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Attach Files</label>
                        <input type="file" name="attachments[]" multiple class="form-control">
                        <small>You can select multiple files</small>
                    </div>


                    <input type="submit" value="Save" class="btn btn-primary">
                </form>
                <form id="redirectForm" action="<?= base_url('ticket/my'); ?>" method="post">
                    <br /><button type="submit" class="btn btn-primary">Back</button>
                </form>
            </div>
            <div class="box-footer"></div>


        </div>

    </section>

</div><!-- /.content-wrapper -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $('#parent_category').change(function() {
        var parent_id = $(this).val();
        $('#child_category').html('<option>Loading...</option>');

        $.ajax({
            url: '<?= site_url('ticket/get_child_categories_ajax') ?>',
            method: 'POST',
            data: {
                parent_id: parent_id
            },
            dataType: 'json',
            success: function(data) {
                //alert(data);
                //alert(data.length);
                if (data.length > 0) {
                    var options = '<option value="">Select Subcategory</option>';
                    $.each(data, function(i, item) {
                        options += '<option value="' + item.id + '">' + item.name + '</option>';
                    });
                    $('#child_category').html(options);
                } else {
                    // No subcategories: default to parent selection
                    $('#child_category').html('<option value="' + parent_id + '">-- No Subcategories --</option>');
                }
            }
        });
    });
</script>