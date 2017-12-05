/*
 * This module is a custom row for rendering an associated product
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'jquery',
        'oro/datagrid/product-row',
        'pim/media-url-generator',
        'pim/template/product/tab/associated-product-row',
        'oro/mediator',
        'pim/security-context'
    ],
    function(
        _,
        $,
        BaseRow,
        mediaUrlGenerator,
        thumbnailTemplate,
        mediator,
        SecurityContext
    ) {
        return BaseRow.extend({
            thumbnailTemplate: _.template(thumbnailTemplate),

            getThumbnailImagePath() {
                const image = this.model.get('image');

                if (undefined === image || null === image) {
                    return '/media/show/undefined/preview';
                }

                return mediaUrlGenerator.getMediaShowUrl(image, 'thumbnail');
            },

            canRemoveAssociation() {
                return SecurityContext.isGranted('pim_enrich_product_association_remove');
            },

            getTemplateOptions() {
                const isProductModel = this.isProductModel();
                const label = this.model.get('label');
                const canRemoveAssociation = this.canRemoveAssociation();

                return {
                    useLayerStyle: isProductModel,
                    label,
                    identifier: this.model.get('identifier'),
                    imagePath: this.getThumbnailImagePath(),
                    canRemoveAssociation
                };
            },

            render() {
                const row = BaseRow.prototype.render.call(this, arguments);

                row.off('click');

                $('.AknIconButton--remove', row).on('click', () => {
                    mediator.trigger('datagrid:unselectModel:association-product-grid', this.model);
                    row.remove();
                });
            },

            /**
             * {@inheritdoc}
             */
            getRenderableColumns() {
                return [this.getCompletenessCellType()];
            }
        });
    }
);
