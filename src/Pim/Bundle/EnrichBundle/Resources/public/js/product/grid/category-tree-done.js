 /**
 * Extension to add a "Done" button under the category tree
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'jquery',
        'pim/form',
        'pim/template/product/grid/category-tree-done'
    ],
    function(
        _,
        __,
        $,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            className: 'AknDefault-thirdColumnButton',
            template: _.template(template),
            events: {
                'click': 'toggleThirdColumn'
            },

            /**
             * {@inheritdoc}
             */
            render() {
                this.$el.append(this.template({
                    label: __('pim_common.done')
                }));
            },

            /**
             * Toggles the third column
             */
            toggleThirdColumn() {
                this.getRoot().trigger('grid:third_column:toggle');
            }
        });
    }
);
