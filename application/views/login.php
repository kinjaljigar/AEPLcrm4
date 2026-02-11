<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section class="content-header">
    <h1>Login</h1>
</section>
<section class="content">
    <div class="row">
        <div class="login-box" style="margin: 0px auto;">
            <div class="login-box-body">
                <p class="login-box-msg">Sign in</p>

                <form method="post" id="loginform">
                    <div class="form-group has-feedback">
                        <input type="text" name="u_username" id="u_username" class="form-control" placeholder="Enter Username">
                        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                        <label id="u_username-error" class="has-error " for="u_username" style="display: none;">This field is required.</label>
                    </div>
                    <div class="form-group has-feedback">
                        <input type="password" name="u_password" id="u_password" class="form-control" placeholder="Password">
                        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                        <label id="u_password-error" class="has-error " for="u_password" style="display: none;">This field is required.</label>
                    </div>
                    <div class="row">
                        <div class="col-xs-8">
                        </div>
                        <!-- /.col -->
                        <div class="col-xs-4">
                            <button type="submit" class="btn btn-primary btn-block btn-flat" id="login_btn">Sign In</button>
                        </div>
                        <!-- /.col -->
                    </div>
                </form>


            </div>
            <!-- /.login-box-body -->
        </div>
    </div>
    <button onclick="handleNotificationClick()" style="display: none;">Show Desktop Notification</button>

</section>

<script>
    function handleNotificationClick() {
        if (!("Notification" in window)) {
            alert("This browser does not support desktop notification.");
            return;
        }

        if (Notification.permission === "granted") {
            showNotification();
        } else if (Notification.permission !== "denied") {
            Notification.requestPermission(function(permission) {
                if (permission === "granted") {
                    showNotification();
                } else {
                    alert("Notification permission denied.");
                }
            });
        } else {
            alert("Notification permission previously denied.");
        }
    }

    function showNotification() {
        new Notification("🔔 Desktop Notification", {
            body: "This is a system-level notification!",
            icon: "https://cdn-icons-png.flaticon.com/512/1827/1827272.png"
        });
    }
    //const publicVapidKey = 'BGB6sDnVslHo6fQgdrBhchA6T2b-nv0UO5A-yMbD5FSZjkGl1--HKL1nFZjgt0Ecw2vM8dOT_GAjC7qxGv51whA'; // Replace with your real one
    function document_ready() {
        jQuery("#loginform").submit(function() {
            login();
            return false;
        });
    }
    async function login() {

        var rules = {
            u_username: {
                required: true
            },
            u_password: {
                required: true
            }
        };

        var form = setValidation('#loginform', rules);
        var isValid = form.valid();
        if (isValid == true) {
            var Data = {
                u_username: $('#u_username').val(),
                u_password: $("#u_password").val(),
            };
            doAjax('api/login', 'post', Data, function(res) {
                if (res.status == "pass") {
                    window.location.href = res.url;
                    return false;
                } else {
                    showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
                }
            });
        } else return false;

    }
</script>