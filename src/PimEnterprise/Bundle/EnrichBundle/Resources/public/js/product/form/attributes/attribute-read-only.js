'use strict';
/**
 * Attribute extension in order to disable an attribute field if this one is read only
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'pim/form',
        'text!pim/template/form/tab/attributes'
    ],
    function ($, _, BaseForm, attributeTemplate) {
        return BaseForm.extend({
            template: _.template(attributeTemplate),
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:field:extension:add', this.addFieldExtension);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritDoc}
             */
            addFieldExtension: function (event) {
                var attribute = event.field.attribute;
                if (attribute.is_read_only) {
                    event.field.setEditable(false);
                }
            }
        });
    }
);
