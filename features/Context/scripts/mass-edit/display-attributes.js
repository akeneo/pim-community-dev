function(attributes) {
    $('button:contains("Select attributes")').click();
    for (var i = 0; i < attributes.length; i++) {
        $('.pimmultiselect input[type="search"]').val(attributes[i]).trigger('keyup');
        var checkbox = $('.pimmultiselect .ui-multiselect-checkboxes')
            .find('li:contains("' + attributes[i] + '") label');
        if (!checkbox || !checkbox.length) {
            return 'Could not find checkbox with label "' + attributes[i] + '"';
        }
        checkbox.click();
    };
    $('.pimmultiselect a.btn:contains("Select")').click();
    return true;
}
