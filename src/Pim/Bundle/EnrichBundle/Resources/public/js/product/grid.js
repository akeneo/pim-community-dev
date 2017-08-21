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
            render: function () {
                if (!this.configured) {
                    return this;
                }

                const { title, gridName } = this.options.config;

                console.log('render the grid', title, gridName)

                this.$el.html(
                    this.template({ title, gridName })
                );

                return this.renderExtensions();
            }
        });
    }
);
