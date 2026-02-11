<?php
defined('BASEPATH') or exit('No direct script access allowed');
$view_data['show_menu'] = true;
$view_data['extra_css'] = array();
$view_data['extra_foot_js'] = array();
switch ($view_data['page']) {
    case 'login':
        $view_data['show_menu'] = false;
        break;
}
if(isset($view_data['plugins']))
{
  foreach($view_data['plugins'] as $key => $val)
  {
    switch($key)
    {
      case 'datatable':
        $view_data['extra_css'][] = 'assets/admin/addons/datatables/dataTables.bootstrap.css';
        $view_data['extra_css'][] = 'assets/admin/addons/datatables/extensions/Responsive/css/dataTables.responsive.css';
        $view_data['extra_foot_js'][] = 'assets/admin/addons/datatables/jquery.dataTables.min.js';
        $view_data['extra_foot_js'][] = 'assets/admin/addons/datatables/extensions/Responsive/js/dataTables.responsive.js';
        $view_data['extra_foot_js'][] = 'assets/admin/addons/datatables/dataTables.bootstrap.min.js';
        break;
      case 'datepicker':
        $view_data['extra_css'][] = 'assets/admin/addons/bootstrap-datepicker/css/bootstrap-datepicker.standalone.min.css';
        $view_data['extra_foot_js'][] = 'assets/admin/addons/bootstrap-datepicker/js/bootstrap-datepicker.min.js';
        //$view_data['extra_foot_js'][] = 'assets/common/js/moment.min.js';
        break;
      case 'form_validation':
        $view_data['extra_foot_js'][] = 'assets/admin/addons/form_validation/jquery.validate.min.js';
        $view_data['extra_foot_js'][] = 'assets/admin/addons/form_validation/additional-methods.min.js';
        $view_data['extra_foot_js'][] = 'assets/admin/addons/form_validation/extra_method.js';
        break;
      case 'momentjs':
          $view_data['extra_foot_js'][] = 'assets/common/js/moment.min.js';
        break;
      case 'chartjs':
        $view_data['extra_foot_js'][] = 'assets/admin/addons/chart/Chart.min.js';
        break;
      case 'select2':
        $view_data['extra_css'][] = 'assets/admin/addons/select2/select2.min.css';
        $view_data['extra_foot_js'][] = 'assets/admin/addons/select2/select2.min.js';
        break;

    } 
  }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $view_data['meta_title']; ?></title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo base_url('assets/favicon.ico'); ?>">
    <!-- Bootstrap 3.3.6 -->
    <link rel="stylesheet" href="<?php echo base_url('assets/admin/addons/bootstrap/css/bootstrap.min.css'); ?>">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
    <!--        Ionicons // may add later if required-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
    <?php foreach ($view_data['extra_css'] as $file) { ?>
        <link href="<?php echo base_url($file); ?>" rel="stylesheet" type="text/css" />
    <?php } ?>
    <link rel="stylesheet" href="<?php echo base_url('assets/admin/css/AdminLTE.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/admin/css/skins/_all-skins.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/admin/css/adminlte-customized.css?d2'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/admin/css/custom-checks.css?03'); ?>">
    <script>
        var sb_base_url = '<?php echo base_url() ?>';
        var holidays = new Object();
    </script>
</head>

<body class="hold-transition skin-blue sidebar-collapse <?php echo $view_data['page'] == 'login' ? 'login-body' : ''; ?>">
    <!-- Site wrapper -->
    <div class="wrapper">
       <div class="content-wrapper">
            <div class="clearfix"></div>
            <?php $this->load->view('' . $view_data['page'], array('view_data' => $view_data)); ?>
        </div>
    </div>
    <div class="loading"><i class="fa fa-gear fa-spin fa-2x"></i></div>
    <script src="<?php echo base_url('assets/admin/addons/jQuery/jQuery-2.2.0.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/admin/addons/bootstrap/js/bootstrap.min.js'); ?>"></script>
    <?php foreach ($view_data['extra_foot_js'] as $file) { ?>
        <script src="<?php echo base_url($file); ?>"></script>
    <?php } ?>
    <script src="<?php echo base_url('assets/admin/js/app.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/common/js/utility.js?5'); ?>"></script>
    <script>
        <?php
        $data = $this->session->flashdata('data');
        if (is_array($data) && count($data) > 0) {
            switch ($data['type']) {
                case 'danger':
                    $title = 'Error!';
                    break;
                case 'success':
                    $title = 'Success!';
                    break;
                case 'warning':
                    $title = 'Alert!';
                    break;
            }
            echo "showModal('ok', '{$data['value']}', '{$title}', 'modal-{$data['type']}','modal-sm')";
        }
        ?>
    </script>
</body>

</html>