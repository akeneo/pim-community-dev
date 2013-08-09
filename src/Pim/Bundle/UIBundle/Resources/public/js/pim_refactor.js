// Activate the form tab specified in the url
if (/^#[a-zA-Z0-9-_]+$/i.test(location.hash)) {
    var activeTab = $('[href=' + location.hash + ']');
    if (activeTab) {
        activeTab.tab('show');
    }
}

// Remove bap 'Loading Application' progressbar
if ($('#progressbar').is(':visible')) {
    $('#progressbar').hide();
    $('#page').show();
}

/* Product edit form js */

    var formAction = "{{ path('pim_product_product_edit', { id: form.vars.value.id, dataLocale: dataLocale }) }}";

    $('#{{ form.vars.id }}').on('submit', function() {
        var hash = $('#form-navbar .nav li.active a').attr('href');
        if (hash) {
            $(this).attr('action', formAction + '&hash=' + hash.substr(1));
        }
        return true;
    });

    $('#locale-switcher a').on('click', function() {
        var hash = $('.tab-groups li.tab.active a').attr('href');
        if (hash) {
            $(this).attr('href', $(this).attr('href') + hash);
        }
    });

    $('button[type="submit"]').click(function() {
        $('#pim_available_product_attributes_attributes').removeAttr('required');
    });

    $('#attribute-buttons .dropdown-menu').click(function (e) {
        e.stopPropagation();
    });

    $('#default_channel').change(function() {
        $('.scopable').scopableField({ defaultScope: $(this).val() });
    });

    $('.dropdown-menu.channel a').click(function (e) {
        e.preventDefault();
        $('.scopable').scopableField($(this).data('action'));
    });

/* End product edit form js */
