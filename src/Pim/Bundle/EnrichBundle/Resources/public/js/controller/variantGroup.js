'use strict';

define(
    [
        'underscore',
        'pim/controller/form',
        'pim/fetcher-registry',
        'pim/optionform',
        'pim/scopable',
        'pim/currencyfield',
        'datepicker',
        'oro/mediator'
    ],
    function (_, FormController, FetcherRegistry, optionform, Scopable, CurrencyField, datepicker, mediator) {
        return FormController.extend({
            events: {
                'submit form.form-horizontal': 'submitForm',
                'submit form#pim_available_attributes': 'clearCache'
            },
            renderTemplate: function () {
                FormController.prototype.renderTemplate.apply(this, arguments);

                _.each(this.$('a.add-attribute-option'), function (optionLink) {
                    optionform.init('#' + optionLink.getAttribute('id'));
                });

                /* jshint nonew:false */
                _.each(this.$('form div.scopable:not(.currency)'), function (field) {
                    new Scopable({ el: field });
                });

                _.each(this.$('form div.currency'), function (field) {
                    new CurrencyField({ el: field });
                });

                _.each(this.$('form input.datepicker:not(.hasPicker)'), function (field) {
                    datepicker.init(field.getAttribute('id'));
                });

                mediator.trigger('pim:reinit');
            },
            clearCache: function () {
                FetcherRegistry.getFetcher('variant-group').clear();
            }
        });
    }
);
