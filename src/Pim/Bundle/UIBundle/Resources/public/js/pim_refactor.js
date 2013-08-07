// Activate the form tab specified in the url
if (/^#[a-zA-Z0-9-_]+$/i.test(location.hash)) {
    var activeTab = $('[href=' + location.hash + ']');
    if (activeTab) {
        activeTab.tab('show');
    }
}

// Disable the oro scrollable container
$('.scrollable-container').removeClass('scrollable-container').css('overflow', 'visible');

// Prevent UniformJS from breaking our stuff
$(document).uniform.restore();

// Apply Select2
$('select').select2({ allowClear: true });

// Apply Select2 multiselect
$('input.multiselect').select2({ tags: $(this).val() });

// Remove bap 'Loading Application' progressbar and partially fix page title regression issue
document.title = $('#page-title').text();
if ($('#progressbar').is(':visible')) {
    $('#progressbar').hide();
    $('#page').show();
}

// DELETE request for delete buttons
$(document).on('click', 'a[data-dialog]', function(event) {
    event.preventDefault();
    $el = $(this);
    var message = $el.data('message');
    var title = $el.data('title');
    if ($el.data('dialog') ==  'confirm') {
        var doAction = function() {
            $el.off('click');
            var $form = $('<form>', { method: 'POST', action: $el.attr('href')});
            $('<input>', { type: 'hidden', name: '_method', value: $el.data('method')}).appendTo($form);
            $form.appendTo('body').submit();
        };

        PimDialog.confirm(message, title, doAction);
    } else {
        PimDialog.alert(message, title);
    }
});

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

    var updated = false;
    $('#updated').hide();

    function formUpdated() {
        updated = true;
        $('#updated').show();
        $('form [id^="{{ form.vars.id }}"]').off('change', formUpdated);
        $('form#{{ form.vars.id }}').off('click', 'ins.jstree-checkbox', formUpdated);
    }

    $('form [id^="{{ form.vars.id }}"]').on('change', formUpdated);
    $('form#{{ form.vars.id }}').on('click', 'ins.jstree-checkbox', formUpdated);

    $(window).on('beforeunload', function(){
        if (updated) {
            return "{{ 'You will lose changes to the product if you leave the page.'|trans }}";
        }
    });

    $('button[type="submit"]').click(function() {
        $('#pim_available_product_attributes_attributes').removeAttr('required');
        $(window).unbind('beforeunload');
    });

    $('#attribute-buttons .dropdown-menu').click(function (e) {
        e.stopPropagation();
    });

    // Destroy Select2 where it's not necessary
    $('#default_channel').select2('destroy');

    $('#default_channel').change(function() {
        $('.scopable').scopableField({ defaultScope: $(this).val() });
    });

    $('.dropdown-menu.channel a').click(function (e) {
        e.preventDefault();
        $('.scopable').scopableField($(this).data('action'));
    });

    $('li.tab.active a').each(function() {
        var paneId = $(this).attr('href');
        $(paneId).addClass('active');
    });

    $('.remove-attribute').each(function() {
        var target = $(this).parent().find('input:not([type="hidden"]):not([class*=select2]), select, textarea').first();
        $(this).insertAfter(target).css('margin-left', 20).attr('tabIndex', -1);
    });

    $('.wysihtml5-toolbar a').attr('tabIndex', -1);

/* End product edit form js */

/* Available attributes form js */
    $('#{{ form.attributes.vars.id }}').select2('destroy');

    $('#{{ form.attributes.vars.id }}').pimMultiselect({
        title: "{{ 'Add attributes'|trans }}",
        placeholder: "{{ 'Search'|trans }}",
        emptyText: "{{ 'There are no more attributes to add'|trans }}",
        appendTo: '#attribute-buttons',
        buttons: {
            "{{ 'Add'|trans }}": function() {
                $('#{{ form.attributes.vars.id }}').multiselect('close');
                if ($('#{{ form.attributes.vars.id }}').val() !== null) {
                    $('#{{ form.vars.id }}').submit();
                }
            }
        }
    });
/* End vailable attributes form js */
