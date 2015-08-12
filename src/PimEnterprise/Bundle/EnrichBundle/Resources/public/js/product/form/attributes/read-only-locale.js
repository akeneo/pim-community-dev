'use strict';

define(
    [
        'jquery',
        'underscore',
        'backbone',
        'pim/form',
        'pim/field-manager',
        'pim/fetcher-registry',
        'oro/mediator'
    ],
    function ($, _, Backbone, BaseForm, FieldManager, FetcherRegistry, mediator) {
        return BaseForm.extend({
            configure: function () {
                this.listenTo(mediator, 'pim_enrich:form:field:extension:add', this.addFieldExtension);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            addFieldExtension: function (event) {
                event.promises.push(
                    FetcherRegistry.getFetcher('permission').fetchAll().then(function (permissions) {
                        var field = event.field;

                        if (field.attribute.localizable) {
                            var localePermission = _.findWhere(permissions.locales, {code: field.context.locale});

                            if (!localePermission.edit) {
                                field.setEditable(false);
                            }
                        }

                        return event;
                    }.bind(this))
                );

                return this;
            }
        });
    }
);
