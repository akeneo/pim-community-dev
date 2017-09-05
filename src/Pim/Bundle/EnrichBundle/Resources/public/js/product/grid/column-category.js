/**
 * Displays the categories selector in grid column
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/template/product/grid/column-category'
    ],
    function(
        _,
        __,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'AknDropdown AknColumn-block category-switcher',
            events: {
                'click': 'toggleThirdColumn'
            },
            isHighlited: false,

            /**
             * {@inheritdoc}
             */
            render() {
                this.$el.html(this.template({
                    categoryLabel: __('pim_enrich.entity.product.category'),
                    isHighlited: this.isHighlited
                }));

                this.renderExtensions();
            },

            /**
             * Toggle the thrid column
             */
            toggleThirdColumn() {
                this.isHighlited = !this.isHighlited;
                this.getRoot().trigger('grid:third_column:toggle');

                this.render();
            }
        });
    }
);
