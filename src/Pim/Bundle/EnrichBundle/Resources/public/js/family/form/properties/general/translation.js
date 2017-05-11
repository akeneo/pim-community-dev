'use strict';

/**
 * Family label translation fields view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'underscore',
        'pim/common/properties/translation',
        'pim/security-context',
        'pim/fetcher-registry',
        'pim/template/form/properties/translation'
    ],
    function (
        _,
        BaseTranslation,
        SecurityContext,
        FetcherRegistry,
        template
    ) {
        return BaseTranslation.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render: function () {
                FetcherRegistry.getFetcher('locale')
                    .search({'activated': true, 'cached': true})
                    .then(function (locales) {
                        this.locales = locales;

                        this.$el.html(this.template({
                            model: this.getFormData(),
                            locales: this.locales,
                            errors: this.validationErrors,
                            label: this.config.label,
                            fieldBaseId: this.config.fieldBaseId,
                            isReadOnly: !SecurityContext.isGranted('pim_enrich_family_edit_properties')
                        }));

                        this.renderExtensions();
                    }.bind(this));

                this.delegateEvents();
            }
        });
    }
);
