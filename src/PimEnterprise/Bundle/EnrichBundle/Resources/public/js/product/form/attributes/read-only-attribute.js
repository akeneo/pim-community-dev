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
                this.listenTo(this.getRoot(), 'pim_enrich:form:field:to-fill-filter', this.addFieldFilter);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritDoc}
             */
            addFieldExtension: function (event) {
                var attribute = event.field.attribute;
                if (!this.isAttributeEditable(attribute)) {
                    event.field.setEditable(false);
                }
            },

            /**
             * Add filter on field if the user doesn't have the right to edit it.
             *
             * @param {object} event
             */
            addFieldFilter: function (event) {
                event.filters.push($.Deferred().resolve(
                    function (attributes) {
                        return _.filter(attributes, function (attribute) {
                            return this.isAttributeEditable(attribute);
                        }.bind(this));
                    }.bind(this)
                ));
            },

            /**
             * Is the current attribute editable ?
             *
             * @param {object} attribute
             *
             * @return {Boolean}
             */
            isAttributeEditable: function (attribute) {
                return !attribute.is_read_only;
            }
        });
    }
);
