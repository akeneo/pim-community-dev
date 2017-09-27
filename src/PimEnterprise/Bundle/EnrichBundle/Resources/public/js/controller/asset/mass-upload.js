/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'jquery',
        'pim/controller/front',
        'pim/form-builder'
    ],
    function (_, $, BaseController, FormBuilder) {
        return BaseController.extend({

            /**
            * @inheritdoc
            */
            renderForm() {
                return FormBuilder.build('pimee-asset-mass-upload').then((form) => {
                    form.setElement(this.$el).render();

                    return form;
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
            }
        });
    }
);
