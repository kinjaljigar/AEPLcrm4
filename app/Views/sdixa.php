<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Satsang Diksha Prasnotari Spardha <?php ?></h1>
    </section>
    <!-- Main content -->
    <?php if($view_data['block_message'] != "") { ?>
        <div class="alert alert-danger alert-dismissible add_margin">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <b><?php echo $view_data['block_message']; ?></b>
         </div>        
    <?php } ?>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Spardhak Yadi</h3>
                    </div>
                    <div class="col-md-5">
                        <?php if (!$view_data['block_yuva'] || !$view_data['block_other']) { ?>
                            <a onclick="return showAddEditForm()" href="javascript://" class="btn btn-primary pull-right">Add New Registration</a>
                        <?php } ?>
                        <a href="<?php echo base_url('spco/index'); ?>" class="btn btn-success pull-right" style="margin-right:10px;display:none1">Download</a>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">Filter: <select class="form-control" id="FilterMid" name="FilterMid" style="width:auto; display:inline"></select> &nbsp; 
                        <select class="form-control" id="FilterKType" name="FilterKType" style="width:auto; display:inline">
                            <option value="">All</option>
                            <option value="Yuvak/Yuvati">Yuvak/Yuvati</option>
                            <option value="Registered">Registered</option>
                            <option value="Sampark Karykar">Sampark Karykar</option>
                        </select>
                        <button type="button" id="main_add_button" onclick="LoadData();" class="btn btn-primary margin">Filter</button>
                    </div>
                </div><br />
                <div class="row">
                    <div class="col-md-12">
                        <table id="datatable" class="table table-bordered table-hover responsive nowrap" width="100% ">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Mandal</th>
                                    <th>Type</th>
                                    <th>Mobile</th>
                                    <th>Age</th>
                                    <th>Exam Language</th>
                                    <th width="120">Action</th>
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
        <input type="hidden" name="ID" id="ID">
        <div class="form-group">
            <label for="Mid">Mandal</label>
            <select class="form-control" id="Mid" name="Mid"></select>
        </div>
        <div class="form-group" id="CheckClick">
            <label for="c_active">Spardhak Type </label> &nbsp;<br />

            <?php if(!$view_data['block_yuva']) { ?>
            <label class="radio_container ktype">Yuvak/Yuvati
                <input type="radio" name="KType" id="KType1" value="Yuvak/Yuvati">
                <span class="checkmark"></span>
            </label>
            <?php } ?>
            <?php if(!$view_data['block_other']) { ?>}
            <label class="radio_container ktype">Registered Karykar
                <input type="radio" name="KType" id="KType2" value="Registered">
                <span class="checkmark"></span>
            </label>
            <label class="radio_container ktype">Sampark Karykar
                <input type="radio" name="KType" id="KType3" value="Sampark Karykar">
                <span class="checkmark"></span>
            </label>
            <?php } ?>
        </div>
        <div class="row" id="ForRegRow" style="display: none;">
            <div class="col-xs-6">
                <div class="form-group">
                    <label for="KDesignation">Designation</label>
                    <select type="text" class="form-control" id="KDesignation" name="KDesignation" value="" placeholder="">
                        <option value="">Select Designation</option>
                        <?php foreach ($view_data['designation'] as $des ) {
                            echo '<option value="'.$des.'"> ' . $des . ' </option>';
                        } ?>
                    </select>
                </div>
            </div>
            <div class="col-xs-6">
                <div class="form-group">
                    <label for="KRegNo">Karykar Number</label>
                    <input type="text" class="form-control" id="KRegNo" name="KRegNo" value="" placeholder="">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="SName">Surname (અટક)</label>
            <input type="text" class="form-control" id="SName" name="SName" value="" placeholder="">
        </div>
        <div class="form-group">
            <label for="FName">Name</label>
            <input type="text" class="form-control" id="FName" name="FName" value="" placeholder="">
        </div>
        <div class="form-group">
            <label for="LName">Father/Husband Name</label>
            <input type="text" class="form-control" id="LName" name="LName" value="" placeholder="">
        </div>
        <div class="form-group">
            <label for="Mobile">Mobile</label>
            <input type="text" class="form-control" id="Mobile" name="Mobile" value="" placeholder="">
        </div>
        <div class="form-group">
            <label for="Age">Age</label>
            <input type="text" class="form-control" id="Age" name="Age" value="" placeholder="">
        </div>
        <div class="form-group">
            <label for="TLanguage">Test Language</label>
            <select class="form-control" id="TLanguage" name="TLanguage">
                <option value="">Select Language</option>
                <option value="Gujarati">Gujarati</option>
                <option value="Hindi">Hindi</option>
                <option value="English">English</option>
            </select>
        </div>
    </div>
    <!-- /.box-body -->

    <div class="box-footer">
        <button type="button" class="btn btn-danger margin pull-right" data-dismiss="modal">Cancel</button>
        <button type="button" id="main_add_button" onclick="saveMain('C');" class="btn btn-primary margin pull-right">Save</button>
        <button type="button" id="main_add_button" onclick="saveMain('NC');" class="btn btn-primary margin pull-right">Save &amp; Add New</button>
    </div>

</div>

<script>
    var STYPE = '';
    var dataTable = null;

    function document_ready() {
        LoadData();
        doAjax('api/drop_get', 'POST', {
            dropobjs: [{
                'type': 'mandal'
            }]
        }, function(res) {
            if (res.status == 'pass') {
                var record = res.data;
                $("#Mid").html(record.mandal);
                $("#FilterMid").html(record.mandal);
                //$("#i_c_id").select2();
            } else {
                showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
            }
        });
    }

    function LoadData() {
        var dtConf = {
            "ajax": {
                url: "<?php echo base_url('api/registrations_get'); ?>",
                method: "post",
                data: {
                    "FilterMid": $("#FilterMid").val(),
                    "FilterKType": $("#FilterKType").val()
                }
            },
            aaSorting: [
                [0, 'asc']
            ],
            "columnDefs": [
                /*{
                               "targets": [0],
                               "searchable": false,
                               'bSortable': true,
                               "orderable": true,
                           }, */
                {
                    "targets": [0, 1, 2, 3, 4, 5],
                    "searchable": false,
                    'bSortable': false,
                    "orderable": false,
                }
            ],
            "oLanguage": {
                "sEmptyTable": "There is not any <b>Registrations</b> added with your criteria.",
            },
        };
        if (dataTable != null) {
            dataTable.destroy();
        }
        dataTable = loadDataTable('#datatable', dtConf);
    }

    function showAddEditForm(id) {
        var id = id == 'undefined' ? 0 : id;
        var html = '<form class="formclass" id="admin_add_form" name="admin_add_form" enctype="multipart/form-data">';
        html += $('.admin_add_modal').html();
        html += '</form>';
        if (parseInt(id) > 0) {
            doAjax('api/registrations_get', 'POST', {
                ID: id
            }, function(res) {
                if (res.status == 'pass') {
                    var record = res.data;
                    showModal('html', html, 'Edit Registration', 'modal', 'modal-md', function() {
                        $.each(record, function(key, value) {
                            if (key == 'KType') {
                                if (value == "Yuvak/Yuvati")
                                {
                                    $('#admin_add_form #KType1').prop('checked', true);
                                }
                                else if(value == "Registered")
                                {
                                    $('#admin_add_form #KType2').prop('checked', true);
                                }
                                else
                                {
                                    $('#admin_add_form #KType3').prop('checked', true);
                                }
                                    
                            } else {
                                $('#admin_add_form').find('#' + key).val(value);
                            }
                        });
                        KType();
                        $("#admin_add_form .ktype").click(function() {
                            KType();
                        });


                        //$("#admin_add_form #i_c_id").select2();
                        //$("#admin_add_form #i_l_id").select2();
                    });
                } else {
                    showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
                }
            });
        } else {
            showModal('html', html, 'Add New Registration', 'modal', 'modal-md', function() {
                /*$("#admin_add_form #i_c_id").select2(); $("#admin_add_form #i_l_id").select2();*/
                $("#admin_add_form .ktype").click(function() {
                    KType();
                });
            });
        }

    }

    function saveMain(sType) {
        STYPE = sType;
        var rules = {
            Mid: {
                required: true
            },
            KType: {
                required: true
            },
            SName: {
                required: true
            },
            FName: {
                required: true
            },
            LName: {
                required: true
            },
            Mobile: {
                required: true,
                digits: true,
                minlength: 10,
                maxlength: 10,
            },
            Age: {
                required: true,
                digits: true,
                minlength: 2,
                maxlength: 2,
            },
            TLanguage: {
                required: true,
            },
        };
        var form = setValidation('#admin_add_form', rules);
        if ($("#admin_add_form input[name='KType']:checked").val() == "Registered") 
        {
            $( "#admin_add_form #KDesignation" ).rules( "add", { required: true } );
            //$( "#admin_add_form #KRegNo" ).rules( "add", { required: true } );
        }
        else
        {
            $( "#admin_add_form #KDesignation" ).rules( "remove" );
            //$( "#admin_add_form #KRegNo" ).rules( "remove" );
        }

        var isValid = form.valid();

        if (isValid == true) {
            var formData = form.serialize();
            //formData.append("sType", sType);

            doAjax('api/registration_add_edit', 'post', formData, function(res) {
                if (res.status == "pass") {
                    if (STYPE == 'NC') {
                        //form[0].reset();
                        $("#admin_add_form #KRegNo").val('');
                        $("#admin_add_form #SName").val('');
                        $("#admin_add_form #FName").val('');
                        $("#admin_add_form #LName").val('');
                        $("#admin_add_form #Mobile").val('');
                        $("#admin_add_form #Age").val('');
                        //$("#admin_add_form #TLanguage").val('');
                        dataTable.ajax.reload();
                        showMessage(res.message, 'admin_add_form', 'error_message', 'success', true);
                        $('#sbModel').animate({
                            scrollTop: 0
                        }, 'slow');
                    } else {
                        showModal('ok', res.message, 'Success!', 'modal-success', 'modal-sm', function() {},
                            function() {
                                form[0].reset();
                                dataTable.ajax.reload();
                            });
                    }
                    
                } else {
                    if (res.type != 'undefined' && res.type == 'popup') {
                        showMessage(res.message, 'admin_add_form', 'error_message', 'danger', true);
                        $('#sbModel').animate({
                            scrollTop: 0
                        }, 'slow');
                    } else {
                        showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
                    }
                }
            });
        }
    }

    function deleteRecord(id) {
        showModal('confirm', 'Are you sure , you want to delete this <b>Registration</b>?', 'Confirm', 'modal-default',
            'modal-sm',
            function() {
                doAjax('api/registration_delete', 'POST', {
                    ID: id
                }, function(res) {
                    if (res.status == 'pass') {
                        showModal('ok', res.message, 'Success', 'modal-success', 'modal-sm', function() {},
                            function() {
                                dataTable.ajax.reload();
                            });
                    } else {
                        showModal('ok', res.message, 'Error', 'modal-danger', 'modal-sm');
                    }
                });
            });
    }

    function KType() {
        if ($("#admin_add_form input[name='KType']:checked").val() == "Registered") {
            $("#admin_add_form #ForRegRow").show();
        } else {
            $("#admin_add_form #ForRegRow").hide();
        }
    }
</script>