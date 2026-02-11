/**
 * Author: 	Jayesh Dhudashia
 * Purpose: This is Main Utility Library File. This will replace SBUtil later.
 */

/*
 * postForm - post a Form to the server (use when uploading files)
 * arguments:
 * url = url to send request
 * data = object with keys for each expected parameter
 * cb = callback function
 * errorcb = error callback function
 * return:
 * nothing
 */
function postForm(url, data, cb, errorcb) {
  $.ajax({
    url: sb_base_url + url,
    type: "POST",
    processData: false,
    contentType: false,
    dataType: "json",
    data: data,
    beforeSend: function () {
      $(".loading").show();
    },
    complete: function () {
      $(".loading").hide();
    },
    error: function (xhr, status) {
      if (errorcb && typeof errorcb == "function") {
        return errorcb(xhr, status);
      } else {
        showModal(
          "ok",
          "Error in handling your request. Kindly reload page.<br/> Error Status: " +
            status,
          "Error",
          "modal-danger",
          "modal-sm"
        );
      }
    },
    success: function (data) {
      if (data.status == "session") {
        showModal(
          "ok",
          data.message,
          "Session Expired",
          "modal-danger",
          "modal-sm",
          function () {},
          function () {
            // window.location.href = sb_base_url + data.path + '/login';
          }
        );
      } else {
        if (cb && typeof cb == "function") {
          cb(data);
        }
      }
    },
  });
}
/**
 * doAjax -make an Ajax call
 * arguments:
 * url = url to send request
 * reqtype = POST or GET
 * data = object with keys for each expected parameter
 * cb = callback function
 * errorcb = error callback function
 * return:
 * nothing
 */
function doAjax(url, reqtype, data, cb, errorcb) {
  $(".loading").show();
  $.ajax({
    url: sb_base_url + url,
    type: reqtype,
    dataType: "json",
    data: data,
    async: false,
    complete: function () {
      $(".loading").hide();
    },
    error: function (xhr, status) {
      if (errorcb && typeof errorcb == "function") {
        return errorcb(xhr, status);
      } else {
        showModal(
          "ok",
          "Error in handling your request. Kindly reload page.<br/> Error Status: " +
            status,
          "Error",
          "modal-danger",
          "modal-sm"
        );
      }
    },
    success: function (data) {
      if (data.status == "session") {
        showModal(
          "ok",
          data.message,
          "Session Expired",
          "modal-danger",
          "modal-sm",
          function () {},
          function () {
            // window.location.href = sb_base_url + data.path + '/login';
          }
        );
      } else {
        if (cb && typeof cb == "function") {
          cb(data);
        }
      }
    },
  });
}
/**
 * showMessage - shows a message box on screen
 * arguments:
 * msg = txt string to be displayed (required)
 * location = container id in which message will be prepanded.
 * id = message id
 * mtype = message type = success, danger, warning, primary
 * closeBtn = true/false = show close button or not
 * return:
 * nothing
 */

function showMessage(msg, location, id, mtype, close) {
  jQuery(".alert-sbremove").remove();
  jQuery("#" + location).prepend(
    '<div class="alert alert-' +
      mtype +
      ' alert-dismissible alert-sbremove" id="' +
      id +
      '">' +
      (close == true
        ? '<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>'
        : "") +
      msg +
      "</div>"
  );
  SmoothJumpElement(location, 30);
}
function SmoothJumpElement(id, extra) {
  jQuery("html, body")
    .stop()
    .animate(
      { scrollTop: jQuery("#" + id).offset().top - extra },
      1000,
      "linear"
    );
}
function SmoothJump(Value) {
  jQuery("html, body").stop().animate({ scrollTop: Value }, 1000);
}
/*
 *      showPopup -shows a modal
 *      Arguments
 *      m_type = HTML message or Dialog (html/ok/close/confirm)
 *      m_html = text for HTML OR Object for dialog (m_html.type = ok/yesno, m_html.html = message)
 *      m_title = Modal Title
 *      m_color =    Dialog color = modal-success, modal-warning, modal-primary, modal-danger, modal-default
 *      m_size = Modal Size = modal-sm or modal-lg
 *      m_on_load = on load call back / Yes call back for confirm
 *      m_on_close = on close call back / No call back for confirm
 *
 */
function showModal(
  m_type,
  m_html,
  m_title,
  m_color,
  m_size,
  m_on_load,
  m_on_close
) {
  var modalId = "sbModel";
  //modalId = $('#'+modalId).hasClass('in')?'sbModel2':modalId;
  //console.log(modalId);
  var m_color = typeof m_color == "undefined" ? "default" : m_color;
  var m_size = typeof m_size == "undefined" ? "" : m_size;
  var m_on_load = typeof m_on_load == "undefined" ? "" : m_on_load;
  var m_on_close = typeof m_on_close == "undefined" ? "" : m_on_close;
  if ($("#" + modalId).length > 0) {
    $("#" + modalId).remove();
    $(".modal-backdrop").remove();
  }
  $("body").append(
    '<div class="modal fade" id="' +
      modalId +
      '" role="dialog" aria-labelledby="myModalLabel">  <div class="modal-dialog ' +
      m_size +
      '" role="document">    <div class="modal-content">      <div class="modal-header">        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>        <h4 class="modal-title" id="myModalLabel">Modal title</h4>      </div>      <div class="modal-body">        ...      </div>        </div>  </div></div>'
  );
  $("#" + modalId)
    .removeClass("modal-danger")
    .removeClass("modal-success")
    .removeClass("modal-warning")
    .removeClass("modal-primary")
    .addClass(m_color);
  $("#" + modalId + " > div")
    .removeClass("modal-lg")
    .removeClass("modal-sm")
    .removeClass("modal-md")
    .addClass(m_size);
  $("#" + modalId + " > div > div > div.modal-footer").remove();
  $("#" + modalId + " .modal-title").html(m_title);
  if (m_type == "html") {
    $("#" + modalId + " .modal-body").html(m_html);
  } else {
    $("#" + modalId + " .modal-body").html(m_html);
    // Change code here for setting up dialog Type
    if (m_type == "confirm") {
      $("#" + modalId + " .modal-body").after(
        '<div class="modal-footer"><button class="btn btn-success pull-left" type="button" id="btnYes">Yes</button> <button data-dismiss="modal" class="btn btn-danger" type="button">No</button></div>'
      );
    } else {
      $("#" + modalId + " .modal-body").after(
        '<div class="modal-footer align-center"><button data-dismiss="modal" class="btn btn-outline" type="button">Close</button></div>'
      );
    }
  }
  $("#" + modalId).modal({ backdrop: "static" });
  if (m_type == "confirm") {
    $("#" + modalId + " .modal-footer #btnYes").click(function (e) {
      if (typeof m_on_load === "function") {
        $(modalId).modal("hide");
        m_on_load();
      }
    });
    $("#" + modalId)
      .modal("show")
      .on("hidden.bs.modal", function (e) {
        if (typeof m_on_close === "function") {
          m_on_close();
        }
      });
  } else {
    $("#" + modalId)
      .modal("show")
      .on("shown.bs.modal", function (e) {
        if (typeof m_on_load === "function") {
          m_on_load();
        }
      })
      .on("hidden.bs.modal", function (e) {
        if (typeof m_on_close === "function") {
          m_on_close();
        }
      });
  }
}

jQuery(document).ready(function () {
  /*
// Loading JS Dynamically for Plugins
    $(".date-picker").each(function () {
        $(this).datepicker({format: 'dd-mm-yyyy'});
    }).on('changeDate', function function_name() {
        $(this).datepicker('hide');
    });*/
  if (typeof document_ready === "function") {
    document_ready();
  }
});
jQuery(".scroll_me").each(function () {
  jQuery(this).slimScroll({
    height: "250px",
  });
});
function setValidation(element, rules, messages, submitHandler, ignore) {
  var ignore = ignore !== undefined ? ignore : ":hidden:not(textarea)";
  var messages = messages == "undefined" ? {} : messages;
  var submitHandler = submitHandler == "undefined" ? "" : submitHandler;
  var form = $(element);
  form.validate({
    errorElement: "label", //default input error message container
    errorClass: "has-error", // default input error message class
    successClass: "has-success",
    rules: rules,
    ignore: ignore,
    messages: messages,
    submitHandler: submitHandler,
    focusInvalid: false,
    invalidHandler: function (form, validator) {
      // var errors = validator.numberOfInvalids();
    },
    success: function (label, element) {
      $(element)
        .parents(".form-group")
        .addClass("has-error")
        .removeClass("has-success");
      $(element).parents(".form-group").children("label.has-error").remove();
      $(element)
        .parents(".form-group")
        .children("div")
        .children("label.has-error")
        .remove();
    },
    highlight: function (element, errorClass, validClass) {
      $(element)
        .parents(".form-group")
        .addClass("has-error")
        .removeClass("has-success");
    },
    unhighlight: function (element, errorClass, validClass) {
      $(element)
        .parents(".form-group")
        .addClass("has-success")
        .removeClass("has-error");
    },
  });
  return form;
}

function loadDataTable(element, newConf) {
  var config = new Object();
  config["processing"] = true;
  config["serverSide"] = true;
  config["searching"] = false;
  config["pageLength"] = 100;
  config["columnDefs"] = new Array();
  config["buttons"] = new Array();
  config["ajax"] = { type: "post" };
  config["aLengthMenu"] = [
    [5, 10, 15, 25, 50, 100, -1],
    [5, 10, 15, 25, 50, 100, "All"],
  ];
  config["oLanguage"] = {
    sProcessing: '<i class="fa fa-gear fa-spin fa-2x"></i>',
  };
  //console.log(newConf);
  $.each(newConf, function (key, val) {
    //console.log(typeof val,key);
    if (config[key] === undefined) {
      config[key] = val;
    } else if (config[key] !== undefined && config[key] != val) {
      //console.log(key,val)
      config[key] = val;
    }
  });
  $.each(config, function (key, val) {
    if (typeof val == "object" && newConf[key] != "undefined") {
      $.extend(config[key], config[key], newConf[key]);
    }
  });
  $.merge(config, newConf);
  var dataTable = $(element).DataTable(config);
  return dataTable;
}
function setDatePicker(id, Obj) {
  if (jQuery.isEmptyObject(Obj)) {
    Obj = { format: "dd-mm-yyyy" };
  } else {
    Obj.format = Obj.format == undefined ? "dd-mm-yyyy" : Obj.format;
  }
  jQuery(id)
    .each(function () {
      $(this).datepicker(Obj);
    })
    .on("changeDate", function function_name() {
      $(this).datepicker("hide");
    });
}
function isStringValid(str) {
  return /^[a-zA-Z(). ]+$/.test(str);
}

function isAlphaNumDash(str) {
  return /[a-zA-Z0-9,.\- ]/.test(str);
}

function isEmailValid(str) {
  return /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(str);
}

function isAlphaNum(str) {
  return /^[a-zA-Z0-9]+$/.test(str);
}

function isNumber(evt) {
  evt = evt ? evt : window.event;
  var charCode = evt.which ? evt.which : evt.keyCode;
  if (charCode > 31 && (charCode < 48 || charCode > 57)) {
    return false;
  }
  return true;
}

String.prototype.replaceAll = function (search, replacement) {
  var target = this;
  return target.split(search).join(replacement);
};

function setTooltip(element) {
  var ele = element == undefined ? '[data-toggle="tooltip"]' : element;
  $(ele).tooltip();
}

$.urlParam = function (name) {
  var results = new RegExp("[?&]" + name + "=([^&#]*)").exec(
    window.location.href
  );
  if (results == null) {
    return null;
  } else {
    return results[1] || 0;
  }
};

$.fn.sb_info = function () {
  this.click(function () {
    showModal(
      "close",
      $(this).attr("data-message"),
      "Information",
      "modal-success",
      "modal-sm"
    );
  });
};
function loadDateRange(startElement, endElement, startDate, onc_cb) {
  startDate = startDate != undefined && startDate != "" ? startDate : false;
  var dateStart = $(startElement)
    .datepicker({
      format: "dd-mm-yyyy",
      startDate: startDate,
      beforeShowDay: function (date) {
        return { enabled: true };
      },
      datesDisabled: [],
    })
    .on("changeDate", function (ev) {
      dateEnd.datepicker("setStartDate", ev.date);
      dateStart.datepicker("hide");
      if (onc_cb && typeof onc_cb == "function") {
        onc_cb();
      }
      dateEnd.focus();
    });
  var dateEnd = $(endElement)
    .datepicker({
      format: "dd-mm-yyyy",
      beforeShowDay: function (date) {
        return { enabled: true };
      },
      datesDisabled: [],
    })
    .on("changeDate", function (ev) {
      dateStart.datepicker("setEndDate", ev.date);
      dateEnd.datepicker("hide");
    });
  //format: "yyyy",
  //viewMode: "years",
  //minViewMode: "years",
  /*var dateStart = $(startElement).datepicker({
     // defaultDate: "+1w",
     changeMonth: true,
     changeYear: true,
     numberOfMonths: 1,
     onClose: function (selectedDate) {
     dateEnd.datepicker("option", "minDate", selectedDate);
     }
     });
     var dateEnd = $(endElement).datepicker({
     //defaultDate: "+1w",
     changeMonth: true,
     numberOfMonths: 1,
     onClose: function (selectedDate) {
     dateStart.datepicker("option", "maxDate", selectedDate);
     }
     });*/
}

function loadSingleDatePickerObject(DtObj, format, startDate) {
  startDate = startDate != undefined && startDate != "" ? startDate : false;
  format = format != undefined && format != "" ? format : "dd-mm-yyyy";
  var dateObj = DtObj.datepicker({
    format: format,
    startDate: startDate,
    beforeShowDay: function (date) {
      return { enabled: true };
    },
    datesDisabled: [],
  }).on("changeDate", function (ev) {
    dateObj.datepicker("hide");
  });
}

function loadSingleDatePicker(element, format, startDate) {
  startDate = startDate != undefined && startDate != "" ? startDate : false;
  format = format != undefined && format != "" ? format : "dd-mm-yyyy";
  var dateObj = $(element)
    .datepicker({
      format: format,
      startDate: startDate,
      beforeShowDay: function (date) {
        return { enabled: true };
      },
      datesDisabled: [],
    })
    .on("changeDate", function (ev) {
      dateObj.datepicker("hide");
    });
}
function loadSingleDateTimePicker(element, format, startDate) {
  startDate = startDate != undefined && startDate != "" ? startDate : false;
  format = format != undefined && format != "" ? format : "D-M-Y H:m";
  $(element).datetimepicker({
    format: format,
    sideBySide: true,
    minDate: startDate,
  });
}

function formatAmount(nStr) {
  nStr += "";
  x = nStr.split(".");
  x1 = x[0];
  x2 = x.length > 1 ? "." + x[1] : "";
  var rgx = /(\d+)(\d{3})/;
  while (rgx.test(x1)) {
    x1 = x1.replace(rgx, "$1" + "," + "$2");
  }
  return "INR " + x1 + x2;
}

function setTooltip(element) {
  var ele = element == undefined ? '[data-toggle="tooltip"]' : element;
  $(ele).tooltip();
}

function isJson(str) {
  try {
    var jsonData = JSON.parse(str);
    if (Object.keys(jsonData).length) {
      return jsonData;
    } else {
      return false;
    }
  } catch (e) {
    return false;
  }
  //return true;
}
