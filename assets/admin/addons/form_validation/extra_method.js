$.validator.addMethod("pan", function (value, element){
    return this.optional(element) || /^[A-Z]{5}\d{4}[A-Z]{1}$/.test(value);
}, "Please Enter a Valid PAN Number.");

$.validator.addMethod("zipcode", function (value, element) {
    return this.optional(element) || /^[0-9]{6}/.test(value);
}, $.validator.format("Please enter valid zip code."));

jQuery.validator.addMethod("gst", function (value, element){
    var str = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
    return this.optional(element) || str.test(value);
}, "Please enter valid GST number.");

$.validator.addMethod("tan", function (value, element){
    return this.optional(element) || /^[A-Z]{4}\d{5}[A-Z]{1}$/.test(value);
}, "Please Enter a Valid TAN Number.");

$.validator.addMethod("matchWithPAN", function (value, element, param)
{
    var target = $(param);
    var compare_value = param;
    if(target.length)compare_value =  target.val();
    if (value)
    {        
        return value.indexOf(compare_value) >= 0;
    }
    else 
    {
        return this.optional(element);
    }
}, "Please enter valid GST no.");

$.validator.addMethod( "pattern", function( value, element, param ) {
	if ( this.optional( element ) ) {
		return true;
	}
	if ( typeof param === "string" ) {
		param = new RegExp( "^(?:" + param + ")$" );
	}
	return param.test( value );
}, "Invalid format." );

$.validator.addMethod("aadhar", function (value, element) {
    return this.optional(element) || /^\d{12}/.test(value);
}, $.validator.format("Please enter valid Aadhar no."));
jQuery.validator.addMethod("validPassword", function (value, element) {
    return this.optional(element) || /(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*])/.test(value);
}, "Password must contain one number, one lowercase and one uppercase letter and one special character.");

// Accept a value from a file input based on a required mimetype
jQuery.validator.addMethod("accept", function (value, element, param) {
    // Split mime on commas in case we have multiple types we can accept
    var typeParam = typeof param === "string" ? param.replace(/\s/g, '').replace(/,/g, '|') : "image/*",
            optionalValue = this.optional(element),
            i, file;
    // Element is optional
    if (optionalValue) {
        return optionalValue;
    }

    if ($(element).attr("type") === "file") {
        // If we are using a wildcard, make it regex friendly
        typeParam = typeParam.replace(/\*/g, ".*");
        // Check if the element has a FileList before checking each file
        if (element.files && element.files.length) {
            for (i = 0; i < element.files.length; i++) {
                file = element.files[i];
                // Grab the mimetype from the loaded file, verify it matches
                if (!file.type.match(new RegExp(".?(" + typeParam + ")$", "i"))) {
                    return false;
                }
            }
        }
    }

    // Either return true because we've validated each file, or because the
    // browser does not support element.files and the FileList feature
    return true;
}, jQuery.validator.format("Kindly select {0} type of file."));

jQuery.validator.addMethod("email_new", function (value, element) {
    var email = value.match(/^(.*)\*/);
    var chkVal = email == null ? value : email[1];
    var cond = /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/.test(chkVal);
    //console.log(chkVal,cond);
    return this.optional(element) || cond;
}, jQuery.validator.format("Please enter at valid email."));

jQuery.validator.addMethod("mobile_new", function (value, element) {
    return this.optional(element) || /^[0-9]{10,11}/.test(value);
}, jQuery.validator.format("Please enter at valid mobile no."));

jQuery.validator.addMethod("notEqualTo", function (value, element, param)
{
    var target = $(param);
    if (value)
    {
        return value != target.val();
    } else
    {
        return this.optional(element);
    }
}, "Does not match");

jQuery.validator.addMethod("Exts", function (value, element, param)
{
    var param = param.split(',');
    value = value.split('.');
    value = value[value.length - 1];
    var isValid = false;
    for (i = 0; i < param.length; i++) {
        if (value == param[i])
        {
            isValid = true;
        }
    }
    return isValid;
}, "Does not match");

jQuery.validator.addMethod("nowhitespace", function (b, a) {
    return this.optional(a) || /^\S+$/i.test(b)
}, "No white space please");

jQuery.validator.addMethod("costVali", function (value, element, params) {
    return this.optional(element) || parseFloat(value) % params == 0;
}, jQuery.validator.format("Enter value in multiples of {0} with minimum {0}."));

jQuery.validator.addMethod( "email_comma", function( value, element) {
        var emails = value.split(',');        
        var reg = /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/;
        var cond = '';
        var $this = this;
        if(emails.length){
                    $.each(emails,function(i,d){
                       cond =  reg.test( d.trim() );  
                       if(!cond){ 
                           return $this.optional( element ) || cond;
                       }
                    });
        }
        return $this.optional( element ) || cond;

}, $.validator.format( "Please enter at valid email." ) );

jQuery.validator.addMethod("max_comma_email", function (value, element, params) {
    var emails = value.split(','); 
    return this.optional(element) || emails.length <= params;
}, jQuery.validator.format("You cannot enter more than {0} emails."));