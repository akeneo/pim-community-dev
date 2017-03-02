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
                this.listenTo(this.getRoot(), 'pim_enrich:form:field:to-fill-filter', this.addFieldFilter);

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
             * Add filter on field if the user doesn't have the right to edit it.
             *
             * @param {object} event
             */
            addFieldFilter: function (event) {
                event.filters.push(FetcherRegistry.getFetcher('permission').fetchAll().then(function (permissions) {
                    return function (attributes) {
                        return _.filter(attributes, function (attribute) {
                            return this.isAttributeEditable(permissions, attribute);
                        }.bind(this));
                    }.bind(this);
                }.bind(this)));
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
                    permissions['attribute_groups'],
                    {code: attribute.group_code}
                );

                return attributeGroupPermission.edit;
            }
        });
    }
);
