'use strict';

define(
    [
        'jquery',
        'underscore',
        'backbone',
        'pim/form',
        'pim/field-manager',
        'pim/fetcher-registry',
        'pim/user-context'
    ],
    function ($, _, Backbone, BaseForm, FieldManager, FetcherRegistry, UserContext) {
        return BaseForm.extend({
            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:field:extension:add', this.addFieldExtension);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Add field extension if the user doesn't have right to edit locale
             *
             * @param {object} event
             */
            addFieldExtension: function (event) {
                event.promises.push(
                    FetcherRegistry.getFetcher('permission').fetchAll().then(function (permissions) {
                        var field = event.field;

                        if (!this.isAttributeEditable(permissions, field.attribute, field.context.locale)) {
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
             * @param  {string}  locale
             *
             * @return {Boolean}
             */
            isAttributeEditable: function (permissions, attribute, locale) {
                if (attribute.localizable) {
                    var localePermission = _.findWhere(permissions.locales, {code: locale});

                    return localePermission.edit;
                }

                return true;
            }
        });
    }
);
