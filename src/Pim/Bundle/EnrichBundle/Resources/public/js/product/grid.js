/**
 * Product grid parent view
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'backbone',
        'pim/form',
        'pim/template/product/index'
    ],
    function (
        _,
        Backbone,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),

            /**
            * {@inheritdoc}
            */
            configure() {
                return BaseForm.prototype.configure.apply(this, arguments);
            },

            render: function () {
                if (!this.configured) {
                    return this;
                }

                const { title } = this.options.config;

                this.$el.html(
                    this.template({ title })
                );

                return this.renderExtensions();
            }
        });
    }
);
