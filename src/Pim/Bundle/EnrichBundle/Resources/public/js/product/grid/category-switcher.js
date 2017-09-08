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
             * @param {String} value.categoryLabel
             * @param {String} value.treeLabel
             */
            updateValue(value) {
                this.categoryLabel = value.categoryLabel;
                this.treeLabel = value.treeLabel;

                this.render();
            }
        });
    }
);
