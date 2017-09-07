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
            config: {
                alias: 'product-grid',
                categoryTreeName: 'pim_enrich_categorytree'
            },
            id: 'tree',
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

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Render the category tree extensions when the datagrid is ready
             */
            setupCategoryTree(urlParams) {
                const categoryFilter = new CategoryFilter(
                    urlParams,
                    this.config.alias,
                    this.config.categoryTreeName,
                    '.filter-item',
                    (value) => {
                        this.valueUpdated(value);
                    }
                );

                this.listenTo(categoryFilter, 'update', function (value) {
                    this.valueUpdated(value);
                });

                return categoryFilter;
            },

            /**
             * Triggers a new event when the value of the category is updated
             *
             * @param {Object} value
             * @param {integer} value.type
             * @param {integer} value.value.categoryId
             * @param {integer} value.value.treeId
             */
            valueUpdated(value) {
                this.getRoot().trigger('pim_enrich:form:category_updated', value);
            }
        });
    }
);
