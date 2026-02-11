<?php
$projects = $view_data['projects'];
$employees = $view_data['employees'];
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>Weekly Work Submission</h1>
    </section>

    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-6">
                        <h3 class="box-title">Weekly Work</h3>
                    </div>
                    <div class="col-md-6">
                        <a onclick="return showAddWeeklyForm()" href="javascript://"
                            class="btn btn-primary pull-right">Add Weekly Work</a>
                    </div>
                </div>
            </div>

            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <label for="project_id">Project</label>
                        <select id="project_id" class="form-control project-select">
                            <option value="">All Projects</option>
                            <?php foreach ($projects as $p): ?>
                                <option value="<?= $p['p_id'] ?>"><?= $p['p_number'] . " - " . $p['p_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>Status</label>
                        <select id="filter_status" class="form-control">
                            <option value="All">All</option>
                            <option value="PAUSE">PAUSE</option>
                            <option value="WIP">WIP</option>
                            <option value="COMPLETED">COMPLETED</option>
                            <option value="HOLD">HOLD</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="from_date">From Date</label>
                        <input type="date" id="from_date" class="form-control" />
                    </div>

                    <div class="col-md-3">
                        <label for="to_date">To Date</label>
                        <input type="date" id="to_date" class="form-control" />
                    </div>

                    <div class="col-md-3" style="margin-top:25px;">
                        <button type="button" id="main_add_button" onclick="LoadWeeklyData();" class="btn btn-primary">
                            Show Weekly Work
                        </button>
                    </div>
                </div>
                <br>

                <div class="row">
                    <div class="col-md-12">
                        <table id="weekly_table" class="table table-bordered table-hover responsive nowrap" width="100%">
                            <thead>
                                <tr>
                                    <th>Week</th>
                                    <th>Project</th>
                                    <th>Task</th>
                                    <th>Submission Date</th>
                                    <!-- <th>Dependency Type</th> -->
                                    <th>No. of Persons</th>
                                    <th>Assigned Employees</th>
                                    <th>Status</th>
                                    <?php
                                    if (in_array($view_data['admin_session']['u_type'], ['Bim Head', 'Master Admin'])): ?>
                                        <th>Created By</th>
                                    <?php endif; ?>
                                    <th width="100">Action</th>

                                </tr>
                            </thead>
                            <tbody class="weekly_list"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- ADD / EDIT FORM MODAL -->
<div class="weekly_add_modal" style="display:none;">
    <div class="box-body">
        <input type="hidden" name="w_id" id="w_id">

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="week_from">Week From</label>
                    <input type="date" class="form-control" id="week_from" name="week_from">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="week_to">Week To</label>
                    <input type="date" class="form-control" id="week_to" name="week_to">
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="p_id">Select Project</label>
                    <select class="form-control" class="project-select" name="p_id" id="p_id" onchange="handleProjectChange()">
                        <option value="">-- Select Project --</option>
                    </select>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="task_name">Task</label>
                    <input type="text" class="form-control" id="task_name" name="task_name" placeholder="Enter task name">
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="submission_date">Submission Date</label>
                    <input type="date" class="form-control" id="submission_date" name="submission_date">
                </div>
            </div>


            <!-- <div class="col-md-6">
                <div class="form-group">
                    <label for="no_of_persons">No. of Persons Assigned</label>
                    <input type="number" class="form-control" id="no_of_persons" name="no_of_persons" min="0" value="0" placeholder="Enter number of persons">
                </div>
            </div> -->
            <div class="col-md-6">
                <div class="form-group">
                    <label>Assign Employees</label>
                    <select name="employee_ids[]" id="employee_ids"
                        class="form-control" multiple>
                        <!-- <option value="">
                            Select Employees
                        </option> -->
                        <?php foreach ($employees as $emp) { ?>
                            <option value="<?= $emp['u_id']; ?>">
                                <?= $emp['u_name']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div><input type="hidden" name="no_of_persons" id="no_of_persons" value="0">
            </div>
        </div>


        <!-- <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="dependency_type">Dependency Type</label>
                    <select class="form-control" id="dependency_type" name="dependency_type" onchange="handleDependencyChange()">
                        <option value="">Select</option>
                        <option value="Internal">Internal</option>
                        <option value="External">External</option>
                    </select>
                </div>
            </div>

            <div class="col-md-6" id="dependency_project_leaders" style="display:none;">
                <div class="form-group">
                    <label for="dep_leader">Select Dependency (Other Leaders)</label>
                    <select class="form-control" id="dep_leader" name="dep_leader[]" multiple>

                    </select>
                </div>
            </div>
        </div>

        <div class="row" id="dependency_text_box" style="display:none;">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="dependency_date">Dependency Date</label>
                    <input type="date" class="form-control" id="dependency_date" name="dependency_date">
                </div>
            </div>
        </div>

        <div class="row" id="dependency_text_box" style="display:none;">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="dependency_text">Dependency Details</label>
                    <textarea class="form-control" id="dependency_text" name="dependency_text" placeholder="Describe dependency..."></textarea>
                </div>
            </div>
        </div> -->


        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="PAUSE">PAUSE</option>
                        <option value="WIP">WIP</option>
                        <option value="COMPLETED">COMPLETED</option>
                        <option value="HOLD">HOLD</option>
                    </select>
                </div>
            </div>
        </div>


        <hr>
        <h4>Dependencies</h4>

        <div id="dependency_list">
            <div class="row dependency_row">
                <input type="hidden" name="dep_id[]" class="dep_id" value="">
                <div class="col-md-2">
                    <input type="text" class="form-control" name="dependency_text[]" placeholder="Dependency Text" required>
                </div>
                <div class="col-md-2">
                    <select class="form-control dep_type" name="dep_type[]">
                        <option value="">Type</option>
                        <option value="Internal">Internal</option>
                        <option value="External">External</option>
                    </select>
                </div>
                <div class="col-md-2 dep_leader_box" style="display:none;">
                    <select class="form-control dep_leader" name="dep_leader[]">
                        <option value="">Select Leader</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-control" name="dep_priority[]">
                        <option value="">Priority</option>
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select class="form-control" name="dep_status[]">
                        <option value="Pending">Pending</option>
                        <!-- <option value="In Progress">In Progress</option> -->
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="dep_target_date[]" placeholder="Completed Date" min="<?= date('Y-m-d'); ?>">
                </div>
                <div class="col-md-1 text-center">
                    <a href="javascript:void(0);" class="btn btn-danger btn-md btn_remove_dep"><i class="fa fa-minus"></i></a>
                </div>
            </div>
        </div>
        <br>
        <a href="javascript:void(0);" class="btn btn-primary btn-md btn_plus_dep"><i class="fa fa-plus"></i> Add Dependency</a>

        <hr>

    </div>

    <div class="box-footer">
        <button type="button" class="btn btn-danger margin pull-right" data-dismiss="modal">Cancel</button>
        <button type="button" id="main_add_button" onclick="saveWeeklyWork()" class="btn btn-primary margin pull-right">Save</button>
    </div>
</div>
<script>
    var weeklyTable = null;

    // function initProjectSelect2() {
    //     $('#p_id').select2({
    //         placeholder: '-- Select Project --',
    //         allowClear: true,
    //         width: '100%',
    //         dropdownParent: $('#weekly_add_form').closest('.modal') // IMPORTANT for modal
    //     });
    // }

    function document_ready() {
        LoadWeeklyData();
        loadAssignedProjects();
        $(document).on('change', '.dep_type', function() {
            const row = $(this).closest('.dependency_row');
            const type = $(this).val();

            if (type === 'Internal') {
                row.find('.dep_leader_box').show();
                loadProjectLeaders(row);
            } else {
                row.find('.dep_leader_box').hide();
            }
        });
        // $('#employee_ids').on('change', function() {
        //     let count = $(this).val() ? $(this).val().length : 0;
        //     $('#no_of_persons').val(count);
        // });
    }
    /*$(document).on('change', '#weekly_add_form #p_id', function() {
        var depType = $('#weekly_add_form #dependency_type').val();
        if (depType === 'Internal') {
            loadProjectLeaders();
        }
    });*/

    function handleProjectChange() {
        var projectId = $('#p_id').val();
        if (!projectId) {
            $('#weekly_add_form .dep_type').val('');
            $('#weekly_add_form .dep_leader').html('<option value="">Select Project First</option>');
            return;
        }
        $('#weekly_add_form .dependency_row:visible').each(function() {
            var depType = $(this).find('.dep_type').val();
            if (depType === 'Internal') {
                loadProjectLeaders($(this), projectId);
            }
        });
    }

    function LoadWeeklyData() {
        var fromDate = $('#from_date').val();
        var toDate = $('#to_date').val();
        var projectId = $('#project_id').val();
        var filter_status = $('#filter_status').val();
        var dtConf = {
            "ajax": {
                url: "<?php echo base_url('api/weeklywork'); ?>",
                method: "post",
                data: {
                    act: "list",
                    from_date: fromDate,
                    to_date: toDate,
                    project_id: projectId,
                    filter_status: filter_status
                }
            },
            pageLength: -1,
            bSort: false,
            "oLanguage": {
                "sEmptyTable": "No Weekly Work Found."
            },
            "columnDefs": [{
                "targets": 2,
                "render": function(data, type, row) {
                    if (type === 'display' && data) {
                        let shortText = data.length > 80 ? data.substr(0, 80) + '...' : data;
                        return '<span title="' + data.replace(/"/g, '&quot;') + '">' + shortText + '</span>';
                    }
                    return data;
                }
            }]

        };
        if (weeklyTable != null) {
            weeklyTable.destroy();
        }
        weeklyTable = loadDataTable('#weekly_table', dtConf);
    }

    function loadAssignedProjects() {
        doAjax('api/drop_get', 'POST', {
            dropobjs: [{
                'type': 'assigned_projects'
            }]
        }, function(res) {
            if (res.status == 'pass') {
                $("#p_id").html(res.data.assigned_projects);
                //initProjectSelect2();
            }
        });
    }

    function showAddWeeklyForm() {
        var html = '<form class="formclass" id="weekly_add_form" name="weekly_add_form">';
        html += $('.weekly_add_modal').html();
        html += '</form>';
        showModal('html', html, 'Add Weekly Work', 'modal', 'modal-lg', function() {
            DependencyClick([]);
            initWeeklyFormValidation();
            // $('#weekly_add_form #p_id').select2({
            //     placeholder: '-- Select Project --',
            //     width: '100%',
            //     dropdownParent: $('.modal') // IMPORTANT for modal
            // });
            initProjectSelect2('#weekly_add_form #p_id', $('.modal'));
        });
    }

    function initWeeklyFormValidation() {
        $("#weekly_add_form").validate({
            rules: {
                week_from: {
                    required: true,
                    date: true
                },
                week_to: {
                    required: true,
                    date: true,
                    greaterThanOrEqual: "#week_from"
                },
                task_name: {
                    required: true,
                },

                // submission_date: {
                //     required: true,
                //     date: true,
                //     betweenDates: ["#week_from", "#week_to"]
                // },
                no_of_persons: {
                    required: true,
                },
                p_id: {
                    required: true
                },
                // dependency_type: {
                //     required: true
                // },
                dependency_text: {
                    required: function() {
                        return $('#dependency_type').val() != "";
                    }
                },
                "dep_leader[]": {
                    required: function() {
                        return $('#dependency_type').val() == "Internal";
                    }
                }
            },
            messages: {
                week_from: {
                    required: "Please select start date of the week."
                },
                week_to: {
                    required: "Please select end date of the week.",
                    greaterThanOrEqual: "End date must be after or same as start date."
                },
                task_name: {
                    required: "Please Enter Task Name.",
                },
                dependency_type: {
                    required: false // optional, but used to control leader rule
                },
                "dep_leader[]": {
                    required: function() {
                        return $('#dependency_type').val() === "Internal";
                    }
                },
                // submission_date: {
                //     required: "Please select submission date.",
                //     betweenDates: "Submission date must be within the selected week."
                // }

                no_of_persons: {
                    required: "Please select no of person worked on this task.",
                },
                p_id: {
                    required: "Please select a project."
                },
                // dependency_type: {
                //     required: "Please select dependency type."
                // },
                dependency_text: {
                    required: "Please provide dependency details."
                },
                "dep_leader[]": {
                    required: "Please select at least one project leader."
                },
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('text-danger');
                element.closest('.form-group').append(error);
            },
            highlight: function(element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element) {
                $(element).removeClass('is-invalid');
            },

        });
        $('#dependency_type').on('change', function() {
            $("#weekly_add_form").validate().element('[name="dep_leader[]"]');
        });
        $.validator.addMethod("greaterThanOrEqual", function(value, element, param) {
            var start = $(param).val();
            if (!start || !value) return true;
            return new Date(value) >= new Date(start);
        }, "Week To must be greater than or equal to Week From.");

        $.validator.addMethod("betweenDates", function(value, element, params) {
            var start = $(params[0]).val();
            var end = $(params[1]).val();
            if (!start || !end || !value) return true;
            var v = new Date(value);
            return v >= new Date(start) && v <= new Date(end);
        }, "Submission date must be between Week From and Week To.");
    }

    function handleDependencyChange() {
        //var depType = $('#dependency_type').val();
        var depType = $('#weekly_add_form #dependency_type').val();
        if (depType === 'Internal') {
            $('#weekly_add_form #dependency_project_leaders').show();
            $('#weekly_add_form #dependency_text_box').show();
            loadProjectLeaders();
        } else if (depType === 'External') {
            $('#weekly_add_form #dependency_project_leaders').hide();
            $('#weekly_add_form #dependency_text_box').show();
        } else {
            $('#weekly_add_form #dependency_project_leaders').hide();
            $('#weekly_add_form #dependency_text_box').hide();
        }
    }

    function loadProjectLeaders(row = null, projectId = null) {
        if (!projectId) {
            projectId = $('.modal #p_id').val();
        }

        if (!projectId) {
            if (row) {
                row.find('.dep_leader').html('<option value="">Select Project First</option>');
            } else {
                $('#weekly_add_form #dep_leader').html('<option value="">Select Project First</option>');
            }
            return;
        }
        if (projectId) {
            doAjax('api/drop_get', 'POST', {
                dropobjs: [{
                    'type': 'project_leaders',
                    'p_id': projectId
                }]
            }, function(res) {
                if (res.status === 'pass') {

                    $('#weekly_add_form #dep_leader').html(res.data.project_leaders);
                } else {
                    $('#weekly_add_form #dep_leader').html('<option value="">No leaders found</option>');
                }
            });
        } else {
            $('#weekly_add_form #dep_leader').html('<option value="">Select Project First</option>');
        }
    }

    function saveWeeklyWork() {
        var form = $('#weekly_add_form');
        var isValid = form.valid();
        if (!isValid) return;

        var formData = new FormData(form[0]);
        formData.append('act', 'add');
        postForm('api/weeklywork', formData, function(res) {
            if (res.status == 'pass') {
                showModal('ok', res.message, 'Success!', 'modal-success', 'modal-sm', function() {}, function() {
                    weeklyTable.ajax.reload();
                });
            } else {
                showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
            }
        });
    }

    function editWeeklyWork(w_id) {
        doAjax('api/weeklywork', 'POST', {
            act: 'edit',
            w_id: w_id
        }, function(res) {
            if (res.status === 'pass') {
                var data = res.data;

                var html = '<form class="formclass" id="weekly_add_form" name="weekly_add_form">';
                html += $('.weekly_add_modal').html();
                html += '</form>';

                showModal('html', html, 'Edit Weekly Work', 'modal', 'modal-lg', function() {
                    setTimeout(function() {
                        var form = $('#weekly_add_form');

                        var $projectSelect = form.find('#p_id').select2({
                            placeholder: '-- Select Project --',
                            width: '100%',
                            dropdownParent: $('.modal')
                        });
                        if (data.p_id) {
                            $projectSelect.val(data.p_id).trigger('change');
                        }

                        form.find('#w_id').val(data.w_id);
                        form.find('#week_from').val(data.week_from);
                        form.find('#week_to').val(data.week_to);
                        form.find('#p_id').val(data.p_id).trigger('change');
                        form.find('#task_name').val(data.task_name);
                        form.find('#submission_date').val(data.submission_date);
                        form.find('#no_of_persons').val(data.no_of_persons);
                        form.find('#dependency_type').val(data.dependency_type).trigger('change');
                        form.find('#status').val(data.status);
                        form.find('#dependency_text').val(data.dependency_text);
                        if (res.assigned_employees && res.assigned_employees.length > 0) {
                            form.find('#employee_ids')
                                .val(res.assigned_employees)
                                .trigger('change');
                        }

                        form.find('#main_add_button')
                            .text('Update')
                            .attr('onclick', 'updateWeeklyWork()');

                        if (data.dependency_type === 'Internal') {
                            setTimeout(function() {
                                loadProjectLeaders();
                                setTimeout(function() {
                                    if (data.dep_leader) {
                                        var leaders = data.dep_leader.split(',');
                                        form.find('#dep_leader').val(leaders);
                                    }
                                }, 400);
                            }, 300);
                        }

                        DependencyClick();

                        setTimeout(function() {
                            populateDependencies(res.dependencies || []);
                        }, 300);
                    }, 300);
                });
            } else {
                showModal('ok', res.message, 'Error', 'modal-danger', 'modal-sm');
            }
        });
    }


    function DependencyClick() {
        const templateRow = jQuery("#weekly_add_form #dependency_list .dependency_row:first");
        templateRow.hide();

        $(document).off('click', '.btn_plus_dep').on('click', '.btn_plus_dep', function() {
            const newRow = templateRow.clone().appendTo("#weekly_add_form #dependency_list").show();
            attachRemoveDepEvent(newRow);
            attachTypeChangeEvent(newRow);
        });

        $(document).off('click', '.btn_remove_dep').on('click', '.btn_remove_dep', function() {
            $(this).closest('.dependency_row').remove();
        });

        $(document).off('change', '.dep_type').on('change', '.dep_type', function() {
            const row = $(this).closest('.dependency_row');
            if ($(this).val() === 'Internal') {
                showDependencyLeaders(row);
            } else {
                row.find('.dep_leader_box').hide();
            }
        });
    }

    function populateDependencies(depData) {
        const container = $("#weekly_add_form #dependency_list");
        const template = container.find('.dependency_row:first');
        container.find('.dependency_row:not(:first)').remove();

        if (Array.isArray(depData) && depData.length > 0) {
            depData.forEach(dep => {
                const newRow = template.clone().appendTo(container).show();
                newRow.find('input[name="dep_id[]"]').val(dep.wd_id || '');
                newRow.find('input[name="dependency_text[]"]').val(dep.dependency_text || '');
                newRow.find('select[name="dep_type[]"]').val(dep.dependency_type || '');
                newRow.find('select[name="dep_priority[]"]').val(dep.priority || '');
                newRow.find('select[name="dep_status[]"]').val(dep.status || '');
                newRow.find('input[name="dep_target_date[]"]').val(
                    dep.target_date && dep.target_date !== '0000-00-00' ? dep.target_date : ''
                );

                if (dep.dependency_type === 'Internal') {
                    showDependencyLeaders(newRow);
                    setTimeout(() => {
                        if (dep.dep_leader_ids) {
                            newRow.find('.dep_leader').val(dep.dep_leader_ids.split(','));
                        }
                    }, 400);
                }

                attachRemoveDepEvent(newRow);
                attachTypeChangeEvent(newRow);
            });
        }
    }



    function updateWeeklyWork() {
        var form = $('#weekly_add_form');
        var isValid = form.valid();
        if (!isValid) return;

        var formData = new FormData(form[0]);
        formData.append('act', 'update');

        postForm('api/weeklywork', formData, function(res) {
            if (res.status === 'pass') {
                showModal('ok', res.message, 'Success', 'modal-success', 'modal-sm', function() {}, function() {
                    weeklyTable.ajax.reload();
                });
            } else {
                showModal('ok', res.message, 'Error', 'modal-danger', 'modal-sm');
            }
        });
    }

    function deleteWeeklyWork(w_id) {
        showModal('confirm', 'Are you sure you want to delete this Weekly Work?', 'Confirm Delete', '', 'modal-sm', function() {
            doAjax('api/weeklywork', 'POST', {
                act: 'delete',
                w_id: w_id
            }, function(res) {
                if (res.status === 'pass') {
                    showModal('ok', res.message, 'Deleted', 'modal-success', 'modal-sm', function() {}, function() {
                        weeklyTable.ajax.reload();
                    });
                } else {
                    showModal('ok', res.message, 'Error', 'modal-danger', 'modal-sm');
                }
            });
        });
    }


    function attachRemoveDepEvent(scope) {
        var selector = scope ? scope.find('.btn_remove_dep') : jQuery(".btn_remove_dep");
        selector.off('click').on('click', function() {
            jQuery(this).closest('.dependency_row').remove();
        });
    }

    function attachTypeChangeEvent(scope) {
        var selector = scope ? scope.find('.dep_type') : jQuery(".dep_type");
        selector.off('change').on('change', function() {
            var type = jQuery(this).val();
            var row = jQuery(this).closest('.dependency_row');

            if (type === 'Internal') {
                showDependencyLeaders(row);
            } else {
                row.find('.dep_leader_box').hide();
            }
        });
    }


    function showDependencyLeaders(row) {
        var projectId = jQuery("#weekly_add_form #p_id").val();
        if (!projectId) {
            alert("Please select a project first.");
            row.find('.dep_type').val('');
            return;
        }

        doAjax('api/drop_get', 'POST', {
            dropobjs: [{
                'type': 'project_leaders',
                'p_id': projectId
            }]
        }, function(res) {
            if (res.status === 'pass') {
                row.find('.dep_leader').html(res.data.project_leaders);
                row.find('.dep_leader_box').show();
            } else {
                row.find('.dep_leader').html('<option value="">No leaders found</option>');
                row.find('.dep_leader_box').show();
            }
        });
    }
</script>