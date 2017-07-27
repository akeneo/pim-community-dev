'use strict';
/**
 * Attribute extension in order to disable an attribute if the form is disabled
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'pim/form',
        'pim/template/form/tab/attributes'
    ],
    function ($, _, BaseForm, attributeTemplate) {
        return BaseForm.extend({
            template: _.template(attributeTemplate),
            readOnly: false,

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:field:extension:add', this.addFieldExtension);
                this.listenTo(this.getRoot(), 'pim_enrich:form:update_read_only', function (readOnly) {
                    this.readOnly = readOnly;
                }.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritDoc}
             */
            addFieldExtension: function (event) {
                if (!this.isAttributeEditable()) {
                    event.field.setEditable(false);
                }
            },

            /**
             * Is the current attribute editable ?
             *
             * @return {Boolean}
             */
            isAttributeEditable: function () {
                return !this.readOnly;
            }
        });
    }
);
