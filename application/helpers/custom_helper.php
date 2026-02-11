<?php

/**
 * This will be used to make detailed debuging of log_message function.
 * @param type $level
 * @param type $message
 */
function log_message_debug($level, $message)
{
    $debug_info = debug_backtrace(false);
    foreach ($debug_info as $key    => $val) {
        if ($val['function'] == 'call_user_func_array') {
            unset($debug_info);
        }
    }
    log_message($level, $message . " | " . json_encode($debug_info));
}
/**
 * This will be used to make to get date form set in session.
 * @param string format 
 * @param string timestamp
 */
function sb_date($format, $time = NULL)
{

    if ($time !== NULL) {
        return date($format, $time);
    }
    $CI = &get_instance();
    if ($CI->session) {
        $today = $CI->session->userdata(SB_TODAY);
        return ($today != NULL ? date($format, $today) : date($format));
    }
    return date($format);
}
function send_otp($mobile, $otp)
{
    $text = "Your OneTimePassword (OTP) is " . $otp;
    $url = "https://api.msg91.com/api/sendhttp.php?mobiles=$mobile&authkey=302815AEHpzxdn5dc542f5&route=4&sender=SDSPCO&message=$text&country=91";
    $data = file_get_contents($url);
}
function convert_db2display($in_date, $keep_time = true)
{
    if ($in_date == "" || $in_date == "0000-00-00") return "";
    $in_date = explode(" ", $in_date);
    $in_date[0] = explode("-", $in_date[0]);
    if ($keep_time)
        return $in_date[0][2] . "-" . $in_date[0][1] . "-" . $in_date[0][0] . ((isset($in_date[1])) ? " " . $in_date[1] : "");
    else
        return $in_date[0][2] . "-" . $in_date[0][1] . "-" . $in_date[0][0];
}
function convert_display2db($in_date)
{
    if ($in_date == "") return "";

    $in_date = explode(" ", $in_date);
    $in_date[0] = explode("-", $in_date[0]);

    return $in_date[0][2] . "-" . $in_date[0][1] . "-" . $in_date[0][0] . ((isset($in_date[1])) ? " " . $in_date[1] : "");
}
function validate_task_files($files, $allowed, $size = 3000)
{
    $errors = '';
    if (isset($files['tf_file'])) {
        $files = $files['tf_file'];
        foreach ($files['name'] as $key => $val) {
            if ($files['error'][$key]) {
                $errors .= 'File ' . $files['name'][$key] . ' could not be uploaded. Error: ' . $files['error'][$key] . '<br/><br/>';
            } else {
                $files['ext'][$key] = explode(".", $files['name'][$key]);
                $files['ext'][$key] = strtolower($files['ext'][$key][count($files['ext'][$key]) - 1]);
                if (in_array($files['ext'][$key], $allowed)) {
                    //[PENDING] add file size checks here.
                } else {
                    $errors .= 'File ' . $files['name'][$key] . ' could not be uploaded. Error: This file type is not allowed.<br/><br/>';
                }
            }
        }
    }
    return $errors;
}
function task_files_old($act = "add", $t_id, $files, $lables)
{
    $CI = &get_instance();
    $CI->load->model("task_model");
    $task_files = $CI->config->item('task_files');
    if ($act == "add") {
        if (isset($files['tf_file'])) {
            $files = $files['tf_file'];
            foreach ($files['name'] as $key => $val) {
                $files['ext'][$key] = explode(".", $files['name'][$key]);
                $files['ext'][$key] = strtolower($files['ext'][$key][count($files['ext'][$key]) - 1]);
                $data = array(
                    'tf_t_id' => $t_id,
                    'tf_title' => $lables[$key + 1],
                    'tf_file_name' => $files['name'][$key],
                    'tf_type' => $files['ext'][$key],
                    'tf_created' => date("Y-m-d H:i:s"),
                );
                $tf_id = $CI->task_model->save_file($data);
                $directory = ceil($tf_id / 500);
                $task_files_final = $task_files . $directory . "/";
                if (!is_dir($task_files_final)) {
                    mkdir($task_files_final);
                }
                move_uploaded_file($files['tmp_name'][$key], $task_files_final . $tf_id);
            }
        }
    }
}
function task_files($act = "add", $t_id, $files, $lables)
{
    $CI = &get_instance();
    $CI->load->model("task_model");
    $task_files = $CI->config->item('task_files');
    if ($act == "add") {
        unset($lables[0]);
        foreach ($lables as $key => $val) {
            if (trim($lables[$key]) == "") continue;
            $data = array(
                'tf_t_id' => $t_id,
                'tf_title' => $lables[$key],
                //'tf_file_name' => $files['name'][$key],
                //'tf_type' => $files['ext'][$key],
                'tf_created' => date("Y-m-d H:i:s"),
            );
            $tf_id = $CI->task_model->save_file($data);
        }
    }
}
function download_file($type = "task", $data)
{
    $CI = &get_instance();
    if ($type == "task") {
        $file = $CI->config->item('task_files');
        $file .= ceil($data['tf_id'] / 500) . '/' . $data['tf_id'];
        header("Content-Type: application/" . $data['tf_type']);
        header("Content-Disposition: attachment; filename=" . $data['tf_file_name']);
    } else { // type = tm
        $file = $CI->config->item('task_message_files');
        $file .= ceil($data['tm_id'] / 500) . '/' . $data['tm_id'];
        header("Content-Type: application/" . $data['tm_file_type']);
        header("Content-Disposition: attachment; filename=" . $data['tm_file_name']);
    }
    readfile($file);
    exit(0);
}
function delete_file($type = "task", $tf_id)
{
    //[PENDING] Validate for task and access
    $CI = &get_instance();
    $CI->load->model("task_model");
    $CI->task_model->remove_files(array('tf_id' => $tf_id));
    $file = $CI->config->item('task_files');
    $file .= ceil($tf_id / 500) . '/' . $tf_id;
    /*
        if(file_exists($file))
        {
            unlink($file);
        }*/
}
function validate_task_message_files($files, $allowed, $size = 3000)
{
    $errors = '';
    if (isset($files['tm_file'])) {
        $file = $files['tm_file'];
        if ($file['error']) {
            $errors .= 'File ' . $file['name'] . ' could not be uploaded. Error: ' . $file['error'] . '<br/><br/>';
        } else {
            $file['ext'] = explode(".", $file['name']);
            $file['ext'] = strtolower($file['ext'][count($file['ext']) - 1]);
            if (in_array($file['ext'], $allowed)) {
                //[PENDING] add file size checks here.
            } else {
                $errors .= 'File ' . $file['name'] . ' could not be uploaded. Error: This file type is not allowed.<br/><br/>';
            }
        }
    }
    return $errors;
}
function MakeTime()
{
    for ($i = 420; $i <= 1380; $i = $i + 15) {
        $h = (int)($i / 60);
        $m = $i % 60;
        if ($m == 0) $m = '00';
        echo '<option value="' . $i . '">' . $h . ':' . $m . '</option>';
    }
}
function RevTime($time)
{
    $h = (int)($time / 60);
    $m = $time % 60;
    if ($m == 0) $m = '00';
    return $h . ':' . $m;
}
function validate_image($file, $allowed, $is_add = true, $size = 3000)
{
    $errors = '';
    if (isset($file['logo_file'])) {
        $file = $file['logo_file'];
        if ($file['error']) {
            $errors .= 'File ' . $file['name'] . ' could not be uploaded. Error: ' . $file['error'] . '<br/><br/>';
        } else {
            $file['ext'] = explode(".", $file['name']);
            $file['ext'] = strtolower($file['ext'][count($file['ext']) - 1]);
            if (in_array($file['ext'], $allowed)) {
                //[PENDING] add file size checks here.
            } else {
                $errors .= 'File ' . $file['name'] . ' could not be uploaded. Error: This file type is not allowed.<br/><br/>';
            }
        }
    }
    return $errors;
}
function save_image($file, $file_name, $size = 3000)
{
    if (isset($file['logo_file'])) {
        $CI = &get_instance();
        $file = $file['logo_file'];
        $logos_files = $CI->config->item('logos_files');
        move_uploaded_file($file['tmp_name'], $logos_files . $file_name . ".jpg");
        /*  
            $file['ext'] = explode(".", $file['name']);
            $file['ext'] = strtolower($file['ext'][count($file['ext'])-1]);
            switch($file['ext'])
            {
                case "png":
                    $imageTmp = imagecreatefrompng($file['tmp_name']);
                    break;
                case "gif":
                    $imageTmp = imagecreatefromgif($file['tmp_name']);
                    break;
                case "jpeg":
                case "jpeg":
                default:
                        $imageTmp = imagecreatefromjpeg($file['tmp_name']);
                    break;
            }
            imagejpeg($imageTmp, $logos_files.$file_name.".jpg", 100);
            imagedestroy($imageTmp);
            unlink($file['tmp_name']);
            */
    }
}
function getLogoURL($id, $type)
{
    $CI = &get_instance();
    $logos_files = $CI->config->item('logos_files');
    $file = $logos_files . $type . "_" . $id . ".jpg";
    if (file_exists($file)) {
        return base_url($file);
    } else {
        return base_url("assets/logos/plogo_0.jpg");
    }
}
