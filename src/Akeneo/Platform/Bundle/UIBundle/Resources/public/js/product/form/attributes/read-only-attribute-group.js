'use strict';

define(
    [
        'jquery',
        'underscore',
        'backbone',
        'pim/form',
        'pim/field-manager',
        'pim/fetcher-registry'
    ],
    function ($, _, Backbone, BaseForm, FieldManager, FetcherRegistry) {
        return BaseForm.extend({
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:field:extension:add', this.addFieldExtension);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            addFieldExtension: function (event) {
                event.promises.push(
                    FetcherRegistry.getFetcher('permission').fetchAll().then(function (permissions) {
                        var field = event.field;

                        if (!this.isAttributeEditable(permissions, field.attribute)) {
                            field.setEditable(false);
                        }

                        return event;
                    }.bind(this))
                );

                return this;
            },

            /**
             * Is the current attribute editable ?
             *
             * @param  {object}  permissions
             * @param  {object}  attribute
             *
             * @return {Boolean}
             */
            isAttributeEditable: function (permissions, attribute) {
                /* jshint sub:true */
                /* jscs:disable requireDotNotation */
                var attributeGroupPermission = _.findWhere(
                    permissions.attribute_groups,
                    {code: attribute.group}
                );

                return attributeGroupPermission.edit;
            }
        });
    }
);
