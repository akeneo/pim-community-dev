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
        'pim/template/product/grid/category-switcher',
        'pim/menu/resizable'
    ],
    function(
        $,
        _,
        __,
        BaseForm,
        template,
        Resizable
    ) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'AknDropdown AknColumn-block category-switcher',
            events: {
                'click': 'toggleThirdColumn'
            },
            isOpen: false,
            categoryLabel: null,
            treeLabel: null,
            outsideEventListener: null,

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:category_updated', this.updateValue);
                this.listenTo(this.getRoot(), 'grid:third_column:toggle', this.updateHighlight);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render() {
                if (null === this.treeLabel || '' === this.treeLabel.trim()) {
                    return;
                }

                this.$el.html(this.template({
                    label: __('pim_enrich.entity.category.uppercase_label'),
                    isOpen: this.isOpen,
                    categoryLabel: this.categoryLabel,
                    treeLabel: this.treeLabel
                }));

                this.renderExtensions();
            },

            /**
             * {@inheritdoc}
             */
            shutdown() {
                Resizable.destroy();

                return BaseForm.prototype.shutdown.apply(this, arguments);
            },

            /**
             * Toggle the thrid column
             */
            toggleThirdColumn() {
                Resizable.set({
                    maxWidth: 500,
                    minWidth: 300,
                    container: '.AknDefault-thirdColumn',
                    storageKey: 'category-switcher'
                });

                this.getRoot().trigger('grid:third_column:toggle');

                if (!this.isOpen) {
                    this.outsideEventListener = this.outsideClickListener.bind(this);
                    document.addEventListener('mousedown', this.outsideEventListener);
                }

                this.isOpen = !this.isOpen;
                this.render();
            },

            /**
             * Closes the criteria if the user clicks on the rest of the document.
             *
             * @param {Event} event
             */
            outsideClickListener(event) {
                const isOpen = $('.AknDefault-thirdColumnContainer--open').length > 0;
                const clickedFilter = $(event.target).closest('.AknFilterBox-addFilterButton').length > 0;

                if (isOpen && clickedFilter) {
                    Resizable.destroy();
                    this.toggleThirdColumn();
                    document.removeEventListener('mousedown', this.outsideEventListener);
                }
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
            },

            /**
             * Updates the highlighted categories
             */
            updateHighlight() {
                this.isHighlited = !this.isHighlited;
                this.render();
            }
        });
    }
);
