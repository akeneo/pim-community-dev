/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'jquery',
        'pim/controller/base',
        'pim/form-builder',
        'pim/user-context'
    ],
    function (_, $, BaseController, FormBuilder, UserContext) {
        return BaseController.extend({
            config: {},

            /**
            * @inheritdoc
            */
            renderRoute() {
                return FormBuilder.build('pimee-asset-index').then((form) => {
                    this.setupLocale();
                    form.setElement(this.$el).render({ gridName: 'asset-grid' });
                });
            },

            /**
            * @inheritdoc
            */
            renderTemplate(content) {
                if (!this.active) {
                    return;
                }

                this.$el.html(content);
            },

            /**
             * Get the locale from url and set to UserContext
             */
            setupLocale() {
                const locale = window.location.hash.split('?dataLocale=')[1];
                if (locale) {
                    UserContext.set('catalogLocale', locale);
                }
            }
        });
    }
);
