 /**
 * Parent extension to render the child extensions for the category tree in the product grid index
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'jquery',
        'pim/form-builder',
        'pim/form',
        'pim/template/category-tree'
    ],
    function(
        _,
        $,
        FormBuilder,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),
            // Remove the id - check if it's being used
            id: 'tree',
            className: 'filter-item',

            attributes: {
                // Get the locale dynamically
                'data-locale':  'en_US',
                'data-name': 'category',
                'data-type': 'tree',
                'data-relatedentity': 'product'
            },

            /**
             * {@inheritdoc}
             */
            render() {
                this.$el.html(this.template({}));

                FormBuilder.buildForm('pim-grid-category-tree').then(function (form) {
                    return form.configure().then(function () {
                        form.setElement('.filter-item').render();
                    });
                }.bind(this));
            }
        });
    }
);
