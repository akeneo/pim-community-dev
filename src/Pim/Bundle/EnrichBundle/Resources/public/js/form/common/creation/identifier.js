'use strict';

/**
 * Identifier field to be added in a creation form
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/form/common/creation/field',
        'pim/user-context',
        'pim/i18n',
        'oro/translator',
        'pim/fetcher-registry',
        'text!pim/template/product-create-error'
    ],
    function (
        _,
        FieldForm,
        UserContext,
        i18n,
        __,
        FetcherRegistry,
        errorTemplate
    ) {
        return FieldForm.extend({
            errorTemplate: _.template(errorTemplate),

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                FieldForm.prototype.initialize.apply(this, arguments);
                this.identifier = 'identifier';
            },

            /**
             * Renders the form
             *
             * @return {Promise}
             */
            render: function () {
                return FetcherRegistry.getFetcher('attribute').getIdentifierAttribute()
                    .then(function (identifier) {
                        this.$el.html(this.template({
                            identifier: this.identifier,
                            label: i18n.getLabel(
                                identifier.labels,
                                UserContext.get('catalogLocale'),
                                identifier.code
                            ),
                            requiredLabel: __('pim_enrich.form.required'),
                            errors: this.getRoot().validationErrors,
                            value: this.getFormModel().get(this.identifier)
                        }));

                        return this;
                    }.bind(this), function () {
                        this.getRoot().trigger('pim_enrich:form:entity:create_product:error');
                        this.$el.html(
                            this.errorTemplate({
                                message: __('error.creating.product')
                            })
                        );
                    }.bind(this));
            }
        });
    }
);
