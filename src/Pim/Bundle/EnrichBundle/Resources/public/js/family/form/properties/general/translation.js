

/**
 * Family label translation fields view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore';
import BaseTranslation from 'pim/common/properties/translation';
import SecurityContext from 'pim/security-context';
import FetcherRegistry from 'pim/fetcher-registry';
import template from 'pim/template/form/properties/translation';
export default BaseTranslation.extend({
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

