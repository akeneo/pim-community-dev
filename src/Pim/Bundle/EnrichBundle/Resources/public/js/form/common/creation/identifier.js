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
        'pim/form/common/creation/field',
        'pim/user-context',
        'pim/i18n',
        'oro/translator',
        'pim/fetcher-registry'
    ],
    function (
        FieldForm,
        UserContext,
        i18n,
        __,
        FetcherRegistry
    ) {
        return FieldForm.extend({

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
                    }.bind(this));
            }
        });
    }
);
