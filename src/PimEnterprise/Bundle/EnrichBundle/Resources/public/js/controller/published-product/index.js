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
            initialize(options) {
                this.config = Object.assign(this.config, options.config || {});

                return BaseController.prototype.initialize.apply(this, arguments);
            },

            /**
            * @inheritdoc
            */
            renderRoute() {
                const { gridName, gridExtension } = this.config;

                return FormBuilder.build(gridExtension).then((form) => {
                    this.setupLocale();
                    this.setupMassEditAttributes();
                    form.setElement(this.$el).render({ gridName });
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
             * Get the locale from the url and set it to the UserContext
             */
            setupLocale() {
                const locale = window.location.hash.split('?dataLocale=')[1];
                if (locale) {
                    UserContext.set('catalogLocale', locale);
                }
            },

            /**
             * Clear selected mass edit attributes
             */
            setupMassEditAttributes() {
                sessionStorage.setItem('mass_edit_selected_attributes', JSON.stringify([]));
            }
        });
    }
);
