$(function() {
    var select = 'form#oro_entity_extend_field_type select#oro_entity_extend_field_type_type';
    $(select).change(function() {
        var selected = $(select + ' option:selected').attr('value');
        $('div#oro_entity_extend_field_type_options_extend input[data-allowedtype]').each(function(index, el) {
            if ($(el).data('allowedtype').indexOf(selected) != -1) {

                $(el).removeClass('hide');
                $(el).parents('.control-group:first').removeClass('hide');
            }
            else {
                $(el).addClass('hide');
                $(el).parents('.control-group:first').addClass('hide');
            }
        })
    })
});
