'use strict';
/**
 * Creation form of a family variant.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'routing',
        'pim/form',
        'pim/template/family-variant/add-variant-form'
    ],
    function(
        $,
        _,
        __,
        Routing,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),

            render() {
                // This can probably be refactored using 'pim/template/common/modal-with-illustration'
                this.$el.html(this.template({
                    okLabel: __('pim_common.create'),
                }));
                this.renderExtensions();
            },

            /**
             * Save the family variant in the backend.
             */
            saveFamilyVariant() {
                this.trigger('pim_enrich:form:entity:pre_save');

                return $.post(
                    Routing.generate('pim_enrich_family_variant_rest_create'),
                    JSON.stringify(this.getFormData())
                ).fail((xhr) => {
                    this.trigger('pim_enrich:form:entity:validation_error', xhr.responseJSON);
                });
            }
        });
    }
);
