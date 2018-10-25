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
        'underscore',
        'oro/translator',
        'pim/i18n',
        'pim/user-context',
        'pim/fetcher-registry',
        'pim/form',
        'pim/template/family-variant/add-variant-form-header'
    ],
    function(
        _,
        __,
        i18n,
        UserContext,
        FetcherRegistry,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render() {
                const catalogLocal = UserContext.get('catalogLocale');

                FetcherRegistry.getFetcher('family')
                    .fetch(this.getFormData().family)
                    .then((family) => {
                        this.$el.html(
                            this.template({
                                __: __,
                                familyName: i18n.getLabel(family.labels, catalogLocal, family.code)
                            })
                        );
                    });
            }
        });
    }
);
