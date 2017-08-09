 /**
 * Extension to set up the category tree filter for the product grid
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
        'oro/datafilter/product_category-filter'
    ],
    function(
        _,
        $,
        FormBuilder,
        BaseForm,
        CategoryFilter
    ) {
        return BaseForm.extend({
            // The id is being used inside product_category-filter
            id: 'tree',
            className: 'filter-item',
            attributes: {
                'data-name': 'category',
                'data-type': 'tree',
                'data-relatedentity': 'product'
            },

            /**
             * @inheritDoc
             */
            configure() {
                this.listenTo(this.getRoot(), 'datagrid:getParams', this.setupCategoryTree);
                BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Render the category tree extensions when the datagrid is ready
             * @TODO - Rewrite datagrid view to remove the need for the event listeners here
             */
            setupCategoryTree(urlParams) {
                return new CategoryFilter(
                    urlParams,
                    'product-grid',
                    'pim_enrich_categorytree',
                    '.filter-item'
                );
            }
        });
    }
);
