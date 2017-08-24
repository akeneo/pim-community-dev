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
            id: 'tree',
            config: {
                gridName: 'product-grid',
                categoryTreeName: 'pim_enrich_categorytree'
            },
            className: 'filter-item',
            attributes: {
                'data-name': 'category',
                'data-type': 'tree',
                'data-relatedentity': 'product'
            },

            /**
             * @inheritdoc
             */
            initialize(options) {
                this.config = Object.assign(this.config, options.config || {});

                return BaseForm.prototype.initialize.apply(this, arguments);
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
             */
            setupCategoryTree(urlParams) {
                return new CategoryFilter(
                    urlParams,
                    this.config.gridName,
                    this.config.categoryTreeName,
                    '.filter-item'
                );
            }
        });
    }
);
