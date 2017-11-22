/*
* This module is a custom row for a product in the datagrid.
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
        mediaUrlGenerator
    ) {
        return BaseRow.extend({
            tagName: 'div',
            rowTemplate: _.template(rowTemplate),
            thumbnailTemplate: _.template(thumbnailTemplate),

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

                this.listenTo(this.model, 'backgrid:selected', (model, checked) => {
                    row.toggleClass('AknGrid-bodyRow--selected', checked);
                });

                return this.delegateEvents();
            },

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
             * Returns the 'thumbnail' size image path for a product OR the dummy image
             *
             * @return {String}
             */
            getThumbnailImagePath() {
                const image = this.model.get('image');

                if (undefined === image || null === image) {
                    return '/media/show/undefined/preview';
                }

                return mediaUrlGenerator.getMediaShowUrl(image.filePath, 'thumbnail');
            },

            /**
             * Renders the completeness, row actions and checkbox cells
             *
             * Adds modifier classes for the completeness cell and the icons
             * inside the row actions cell.
             *
             * @param  {HTMLElement} row
             */
            renderCells(row) {
                const type = this.getCompletenessCellType();
                const columnsToRender = [type, 'massAction', ''];

                this.cells.forEach(cell => {
                    const columnName = cell.column.get('name');

                    if (false === columnsToRender.includes(columnName)) {
                        return;
                    }

                    const cellElement = cell.render().el;

                    if (columnName === type) {
                        $(cellElement).addClass('AknBadge--topRight');
                    } else if (columnName === '') {
                        $('.AknIconButton', cellElement).addClass('AknIconButton--white');
                    }

                    row.append(cellElement);
                });
            }
        });
    });
