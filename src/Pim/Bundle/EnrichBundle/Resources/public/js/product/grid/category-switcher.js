/**
 * Displays the categories selector in grid column
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/template/product/grid/category-switcher'
    ],
    function(
        $,
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
            categoryLabel: null,
            treeLabel: null,

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:category_updated', this.updateValue);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render() {
                this.$el.html(this.template({
                    label: __('pim_enrich.entity.product.category'),
                    isHighlited: this.isHighlited,
                    categoryLabel: this.categoryLabel,
                    treeLabel: this.treeLabel
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
            },

            /**
             * Updates the current category and tree
             *
             * @param {Object} value
             * @param {integer} value.type
             * @param {integer} value.value.categoryId
             * @param {integer} value.value.treeId
             */
            updateValue(value) {
                this.categoryLabel = this.getCategoryLabel(value.value.categoryId);
                this.treeLabel = this.getTreeLabel(value.value.treeId);

                this.render();
            },

            /**
             * Get the category label from its id.
             * We search the matching DOM element in the JStree plugin directly, because it does not exist any fetcher
             * able to get the label from its id.
             *
             * @param {integer} id
             *
             * @returns {String}
             */
            getCategoryLabel(id) {
                return this.trimCount($('#node_' + id).text().trim());
            },

            /**
             * Get the tree label from its id.
             * See this.getCategoryLabel
             *
             * @param {integer} id
             *
             * @returns {String}
             */
            getTreeLabel(id) {
                return this.trimCount($('#tree_toolbar .select2-chosen').text().trim());
            },

            /**
             * Deletes the count of the category and the tree to only keep the label
             * For example, "Audio (123)" will return "Audio".
             *
             * @param {String} str
             *
             * @returns {String}
             */
            trimCount(str) {
                return str.replace(/(.*) \(\d+\)/, (match, text) => text);
            }
        });
    }
);
