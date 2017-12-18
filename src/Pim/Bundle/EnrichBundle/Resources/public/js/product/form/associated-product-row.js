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

            /**
             * Returns true if the user has the right to remove an association,
             * hide the remove button in this case.
             *
             * @return {Boolean}
             */
            canRemoveAssociation() {
                return SecurityContext.isGranted('pim_enrich_associations_remove');
            },

            /**
             * {@inheritdoc}
             */
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

            /**
             * {@inheritdoc}
             */
            render() {
                BaseRow.prototype.render.call(this, arguments);

                const row = this.renderedRow;

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
