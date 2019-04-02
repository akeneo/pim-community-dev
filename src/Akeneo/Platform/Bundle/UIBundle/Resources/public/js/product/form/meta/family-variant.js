'use strict';
/**
 * Meta extension to display family variant label
 *
 * @author    Adrien Petremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/template/product/meta/family-variant',
        'pim/user-context',
        'pim/i18n'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        template,
        UserContext,
        i18n
    ) {
        return BaseForm.extend({
            className: 'AknColumn-block',

            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(UserContext, 'change:catalogLocale', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                const entity = this.getFormData();
                const familyVariant = entity.meta.family_variant;

                if (null === familyVariant) {
                    return this;
                }

                const label = i18n.getLabel(
                    familyVariant.labels,
                    UserContext.get('catalogLocale'),
                    entity.meta.family_variant.code
                );

                this.$el.html(
                    this.template({
                        title: __('pim_enrich.entity.family_variant.short_label'),
                        familyVariantLabel: label
                    })
                );

                BaseForm.prototype.render.apply(this, arguments);
            }
        });
    }
);
