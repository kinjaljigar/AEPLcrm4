<?php

/**
 * Custom Helper Functions for CI4
 * Migrated from CodeIgniter 3
 */

if (!function_exists('log_message_debug')) {
    /**
     * Detailed debugging of log_message function
     *
     * @param string $level
     * @param string $message
     */
    function log_message_debug(string $level, string $message): void
    {
        $debug_info = debug_backtrace(false);
        foreach ($debug_info as $key => $val) {
            if ($val['function'] == 'call_user_func_array') {
                unset($debug_info);
            }
        }
        log_message($level, $message . " | " . json_encode($debug_info));
    }
}

if (!function_exists('sb_date')) {
    /**
     * Get date from set in session
     *
     * @param string $format
     * @param int|null $time
     * @return string
     */
    function sb_date(string $format, ?int $time = null): string
    {
        if ($time !== null) {
            return date($format, $time);
        }

        $session = session();
        if ($session->has('SB_TODAY')) {
            $today = $session->get('SB_TODAY');
            return date($format, $today);
        }

        return date($format);
    }
}

if (!function_exists('send_otp')) {
    /**
     * Send OTP via SMS
     *
     * @param string $mobile
     * @param string $otp
     */
    function send_otp(string $mobile, string $otp): void
    {
        $text = "Your OneTimePassword (OTP) is " . $otp;
        $url = "https://api.msg91.com/api/sendhttp.php?mobiles=$mobile&authkey=302815AEHpzxdn5dc542f5&route=4&sender=SDSPCO&message=$text&country=91";
        file_get_contents($url);
    }
}

if (!function_exists('convert_db2display')) {
    /**
     * Convert database date format to display format
     *
     * @param string $in_date
     * @param bool $keep_time
     * @return string
     */
    function convert_db2display(string $in_date, bool $keep_time = true): string
    {
        if ($in_date == "" || $in_date == "0000-00-00") {
            return "";
        }

        $in_date = explode(" ", $in_date);
        $in_date[0] = explode("-", $in_date[0]);

        if ($keep_time) {
            return $in_date[0][2] . "-" . $in_date[0][1] . "-" . $in_date[0][0] . ((isset($in_date[1])) ? " " . $in_date[1] : "");
        } else {
            return $in_date[0][2] . "-" . $in_date[0][1] . "-" . $in_date[0][0];
        }
    }
}

if (!function_exists('convert_display2db')) {
    /**
     * Convert display date format to database format
     *
     * @param string $in_date
     * @return string
     */
    function convert_display2db(string $in_date): string
    {
        if ($in_date == "") {
            return "";
        }

        $in_date = explode(" ", $in_date);
        $in_date[0] = explode("-", $in_date[0]);

        return $in_date[0][2] . "-" . $in_date[0][1] . "-" . $in_date[0][0] . ((isset($in_date[1])) ? " " . $in_date[1] : "");
    }
}

if (!function_exists('validate_task_files')) {
    /**
     * Validate task files
     *
     * @param array $files
     * @param array $allowed
     * @param int $size
     * @return string
     */
    function validate_task_files(array $files, array $allowed, int $size = 3000): string
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
                        // File type is allowed
                    } else {
                        $errors .= 'File ' . $files['name'][$key] . ' could not be uploaded. Error: This file type is not allowed.<br/><br/>';
                    }
                }
            }
        }
        return $errors;
    }
}

if (!function_exists('task_files')) {
    /**
     * Handle task file uploads
     *
     * @param string $act
     * @param int $t_id
     * @param array $files
     * @param array $lables
     */
    function task_files(string $act = "add", int $t_id, array $files, array $lables): void
    {
        if ($act == "add") {
            $taskModel = new \App\Models\TaskModel();
            $config = config('App');
            $task_files = FCPATH . 'uploads/task_files/'; // Default path

            unset($lables[0]);
            foreach ($lables as $key => $val) {
                if (trim($lables[$key]) == "") {
                    continue;
                }

                $data = [
                    'tf_t_id' => $t_id,
                    'tf_title' => $lables[$key],
                    'tf_created' => date("Y-m-d H:i:s"),
                ];

                $taskModel->saveFile($data);
            }
        }
    }
}

if (!function_exists('download_file')) {
    /**
     * Download file
     *
     * @param string $type
     * @param array $data
     */
    function download_file(string $type = "task", array $data): void
    {
        if ($type == "task") {
            $file = FCPATH . 'uploads/task_files/';
            $file .= ceil($data['tf_id'] / 500) . '/' . $data['tf_id'];
            header("Content-Type: application/" . $data['tf_type']);
            header("Content-Disposition: attachment; filename=" . $data['tf_file_name']);
        } else { // type = tm
            $file = FCPATH . 'uploads/task_message_files/';
            $file .= ceil($data['tm_id'] / 500) . '/' . $data['tm_id'];
            header("Content-Type: application/" . $data['tm_file_type']);
            header("Content-Disposition: attachment; filename=" . $data['tm_file_name']);
        }
        readfile($file);
        exit(0);
    }
}

if (!function_exists('delete_file')) {
    /**
     * Delete task file
     *
     * @param string $type
     * @param int $tf_id
     */
    function delete_file(string $type = "task", int $tf_id): void
    {
        $taskModel = new \App\Models\TaskModel();
        $taskModel->removeFiles(['tf_id' => $tf_id]);

        $file = FCPATH . 'uploads/task_files/';
        $file .= ceil($tf_id / 500) . '/' . $tf_id;
        // Optionally uncomment to physically delete file
        // if(file_exists($file)) {
        //     unlink($file);
        // }
    }
}

if (!function_exists('validate_task_message_files')) {
    /**
     * Validate task message files
     *
     * @param array $files
     * @param array $allowed
     * @param int $size
     * @return string
     */
    function validate_task_message_files(array $files, array $allowed, int $size = 3000): string
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
                    // File type is allowed
                } else {
                    $errors .= 'File ' . $file['name'] . ' could not be uploaded. Error: This file type is not allowed.<br/><br/>';
                }
            }
        }
        return $errors;
    }
}

if (!function_exists('MakeTime')) {
    /**
     * Generate time options for dropdown
     */
    function MakeTime(): void
    {
        for ($i = 420; $i <= 1380; $i = $i + 15) {
            $h = (int)($i / 60);
            $m = $i % 60;
            if ($m == 0) {
                $m = '00';
            }
            echo '<option value="' . $i . '">' . $h . ':' . $m . '</option>';
        }
    }
}

if (!function_exists('RevTime')) {
    /**
     * Reverse time from minutes to H:M format
     *
     * @param int $time
     * @return string
     */
    function RevTime(int $time): string
    {
        $h = (int)($time / 60);
        $m = $time % 60;
        if ($m == 0) {
            $m = '00';
        }
        return $h . ':' . $m;
    }
}

if (!function_exists('validate_image')) {
    /**
     * Validate image file
     *
     * @param array $file
     * @param array $allowed
     * @param bool $is_add
     * @param int $size
     * @return string
     */
    function validate_image(array $file, array $allowed, bool $is_add = true, int $size = 3000): string
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
                    // File type is allowed
                } else {
                    $errors .= 'File ' . $file['name'] . ' could not be uploaded. Error: This file type is not allowed.<br/><br/>';
                }
            }
        }
        return $errors;
    }
}

if (!function_exists('save_image')) {
    /**
     * Save uploaded image
     *
     * @param array $file
     * @param string $file_name
     * @param int $size
     */
    function save_image(array $file, string $file_name, int $size = 3000): void
    {
        if (isset($file['logo_file'])) {
            $file = $file['logo_file'];
            $logos_files = FCPATH . 'assets/logos/';
            move_uploaded_file($file['tmp_name'], $logos_files . $file_name . ".jpg");
        }
    }
}

if (!function_exists('getLogoURL')) {
    /**
     * Get logo URL
     *
     * @param int $id
     * @param string $type
     * @return string
     */
    function getLogoURL(int $id, string $type): string
    {
        $logos_files = FCPATH . 'assets/logos/';
        $file = $logos_files . $type . "_" . $id . ".jpg";

        if (file_exists($file)) {
            return base_url('assets/logos/' . $type . "_" . $id . ".jpg");
        } else {
            return base_url("assets/logos/plogo_0.jpg");
        }
    }
}
