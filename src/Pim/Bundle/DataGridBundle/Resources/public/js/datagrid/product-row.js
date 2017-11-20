/*
* This module renders a custom 'gallery' view for a product in a datagrid.
*
* @author    Tamara Robichet <tamara.robichet@akeneo.com>
* @copyright 2017 Akeneo SAS (http://www.akeneo.com)
* @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
define(
    [
        'jquery',
        'underscore',
        'backgrid',
        'oro/datagrid/row',
        'pim/template/datagrid/row/product',
        'pim/template/datagrid/row/product-thumbnail',
        'pim/media-url-generator'
    ],
    function(
        $,
        _,
        Backgrid,
        BaseRow,
        rowTemplate,
        thumbnailTemplate,
        MediaUrlGenerator
    ) {
        return BaseRow.extend({
            tagName: 'div',
            rowTemplate: _.template(rowTemplate),
            thumbnailTemplate: _.template(thumbnailTemplate),

            /**
             * Returns true if the model is a product model
             * @return {Boolean}
             */
            isProductModel() {
                return this.model.get('document_type') === 'product_model';
            },

            /**
             * Get the name of the completeness cell based on product type
             * @return {String}
             */
            getCompletenessCellType() {
                return this.isProductModel() ? 'complete_variant_products' : 'completeness';
            },

            /**
             * If the row contains a checked checkbox, set the selected class
             * @param {HTMLElement} row
             */
            setCheckedClass(row) {
                const isChecked = $('input[type="checkbox"]:checked', row).length;
                row.toggleClass('AknGrid-bodyRow--selected', 1 === isChecked);
            },

            /**
             * Returns the 'thumbnail' size image path for a product OR the dummy image
             *
             * @return {String}
             */
            getThumbnailImagePath() {
                const image = this.model.get('image');

                if (null === image) {
                    return '/media/show/undefined/preview';
                }

                return MediaUrlGenerator.getMediaShowUrl(image.filePath, 'thumbnail');
            },

            /**
             * Renders the completeness, row actions and checkbox cells
             * @param  {HTMLElement} row
             */
            renderCells(row) {
                const type = this.getCompletenessCellType();
                const columnNames = [type, 'massAction', ''];
                const cells = this.cells.filter(cell => {
                    return columnNames.includes(cell.column.get('name'));
                });

                cells.forEach(cell => row.append(cell.render().el));
            },

            /**
             * {@inheritdoc}
             */
            render() {
                const label = this.model.get('label');
                const isProductModel = this.isProductModel();
                const row = $(this.rowTemplate({ isProductModel, label }));

                const thumbnail = this.thumbnailTemplate({
                    isProductModel,
                    label,
                    identifier: this.model.get('identifier'),
                    imagePath: this.getThumbnailImagePath()
                });

                row.empty().append(thumbnail);
                this.renderCells(row);
                this.$el.empty().html(row);

                row.on('click', this.onClick.bind(this));
                row.on('change', 'input[type="checkbox"]', this.setCheckedClass.bind(this, row));

                return this.delegateEvents();
            }
        });
    });
