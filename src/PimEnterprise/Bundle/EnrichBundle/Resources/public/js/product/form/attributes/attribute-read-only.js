'use strict';

define(
    [
        'jquery',
        'underscore',
        'pim/form',
        'pim/field-manager',
        'pim/fetcher-registry',
        'oro/mediator',
        'text!pim/template/product/tab/attributes'
    ],
    function ($, _, BaseForm, FieldManager, FetcherRegistry, mediator, attributeTemplate) {
        return BaseForm.extend({
            template: _.template(attributeTemplate),
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:field:extension:add', this.addFieldExtension);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            addFieldExtension: function (event) {
                var attribute = event.field.attribute;
                if (attribute.is_read_only) {
                    event.field.setEditable(false);

                    return;
                }

                return this;
            }
        });
    }
);
