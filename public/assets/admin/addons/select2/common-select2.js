function initProjectSelect2(selector, parent = null) {
    let options = {
        placeholder: 'Select Project',
        allowClear: true,
        //width: '100%'
    };

    // Important for modal support
    if (parent) {
        options.dropdownParent = parent;
    }

    $(selector).select2(options);
}