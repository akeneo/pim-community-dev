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
        'oro/datagrid/row',
        'pim/template/datagrid/row/product',
        'pim/template/datagrid/row/product-thumbnail',
        'pim/media-url-generator'
    ],
    function(
        $,
        _,
        BaseRow,
        rowTemplate,
        thumbnailTemplate,
        mediaUrlGenerator
    ) {
        return BaseRow.extend({
            tagName: 'div',
            rowTemplate: _.template(rowTemplate),
            thumbnailTemplate: _.template(thumbnailTemplate),
            renderedRow: null,

            /**
             * Return the columns for the cells that should be rendered
             *
             * @return {Array} An array of column names
             */
            getRenderableColumns() {
                return ['massAction', 'rowActions'];
            },

            /**
             * Return an object containing the template options
             *
             * @return {Object}
             */
            getTemplateOptions() {
                const isProductModel = this.isProductModel();
                const label = this.model.get('label');

                return {
                    useLayerStyle: isProductModel,
                    label,
                    identifier: this.model.get('identifier'),
                    imagePath: this.getThumbnailImagePath(),
                    completenessText: this.getCompletenessText(),
                    completenessClass: this.getCompletenessClass(),
                };
            },

            /**
             * {@inheritdoc}
             */
            render() {
                const templateOptions = this.getTemplateOptions();
                const row = $(this.rowTemplate(templateOptions));
                const thumbnail = this.thumbnailTemplate(templateOptions);

                row.html(thumbnail);

                this.renderCells(row);
                this.$el.empty().html(row);

                row.on('click', this.onClick.bind(this));

                this.listenTo(this.model, 'backgrid:selected', (model, checked) => {
                    row.toggleClass('AknGrid-bodyRow--selected', checked);
                });

                this.delegateEvents();

                this.renderedRow = row;

                return this;
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
                const columnsToRender = this.getRenderableColumns();

                this.cells.forEach(cell => {
                    const columnName = cell.column.get('name');

                    if (false === columnsToRender.includes(columnName)) {
                        return;
                    }

                    const cellElement = cell.render().el;
                    this.setCellModifiers(columnName, cellElement);

                    row.append(cellElement);
                });
            },

            /**
             * Set modifiers on cells within a cell element
             *
             * @param {String} columnName  The name of a column e.g. completeness
             * @param {HTMLElement} cellElement The element for the cell
             */
            setCellModifiers(columnName, cellElement) {
                const type = this.getCompletenessCellType();

                if (columnName === type) {
                    $(cellElement).addClass('AknBadge--topRight');
                } else if (columnName === 'rowActions') {
                    $('.AknIconButton', cellElement).addClass('AknIconButton--white');
                }
            },

            getCompletenessText() {
                if (this.isProductModel()) {
                    const complete = this.model.get('complete_variant_products').complete;
                    const total = this.model.get('complete_variant_products').total;

                    return complete + ' / ' + total;
                } else {
                    const completeness = this.model.get('completeness');
                    if (null !== completeness) {
                        return completeness + '%';
                    }
                }

                return null;
            },

            getCompletenessClass() {
                if (this.isProductModel()) {
                    const complete = this.model.get('complete_variant_products').complete;
                    const total = this.model.get('complete_variant_products').total;
                    if (complete === total) {
                        return 'AknBadge--success';
                    } else if (complete === 0) {
                        return 'AknBadge--important';
                    } else {
                        return 'AknBadge--warning';
                    }
                } else {
                    const completeness = this.model.get('completeness');
                    if (null !== completeness) {
                        if (completeness <= 0) {
                            return 'AknBadge--important';
                        } else if (completeness >= 100) {
                            return 'AknBadge--success';
                        } else {
                            return 'AknBadge--warning';
                        }
                    }
                }

                return null;
            }
        });
    });
