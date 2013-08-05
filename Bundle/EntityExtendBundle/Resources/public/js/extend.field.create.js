$(function() {
    var select = 'form#oro_entity_extend_field_type select#oro_entity_extend_field_type_type';
    $(select).change(function() {

        var selected = $(select + ' option:selected').attr('value');
        $.post($(select).data('loadformroute'), {});
    })
});
