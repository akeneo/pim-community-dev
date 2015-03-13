"use strict";

define(['pim/field', 'underscore', 'text!pim/template/product/field/simple-select', 'routing', 'jquery.select2'], function (Field, _, fieldTemplate, Routing) {
    return Field.extend({
        fieldTemplate: _.template(fieldTemplate),
        fieldType: 'simple-select',
        events: {
            'change input': 'updateModel'
        },
        renderInput: function(context) {
            return this.fieldTemplate(context);
        },
        render: function() {
            Field.prototype.render.apply(this, arguments);

            var $elem = this.$('input.select-field');

            $elem.select2('destroy').select2({
                ajax: {
                    url: Routing.generate(
                        'pim_ui_ajaxentity_list',
                        {
                            'class': 'PimCatalogBundle:AttributeOption',
                            'dataLocale': 'en_US',
                            'collectionCode': this.attribute.code,
                            'options': {'type': 'code'}
                        }
                    ),
                    cache: true,
                    data: function(term) {
                        return {search: term};
                    },
                    results: function(data) {
                        return data;
                    }
                },
                initSelection: function(element, callback) {
                    var id = $(element).val();
                    if (id !== '') {
                        callback({'id': id, 'text': id});
                    }
                },
                placeholder: ' ',
                allowClear: true
            });
        },
        updateModel: function (event) {
            var data = event.currentTarget.value;
            this.setCurrentValue(data);
        }
    });
});
