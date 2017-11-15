define([
    'jquery',
    'underscore',
    'backbone',
    'oro/translator',
    'pim/form',
    'pim/template/datagrid/display-selector'
], function (
    $,
    _,
    Backbone,
    __,
    BaseForm,
    template
) {
    return BaseForm.extend({
        className: 'AknDisplaySelector',
        config: {},
        template: _.template(template)
    });
});
