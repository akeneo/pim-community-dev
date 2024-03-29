/*
 * This module is a custom row for rendering an associated product
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
  'underscore',
  'jquery',
  'oro/datagrid/product-row',
  'pim/media-url-generator',
  'pim/template/product/tab/associated-product-row',
  'oro/mediator',
  'pim/security-context',
  'pim/router',
], function (_, $, BaseRow, mediaUrlGenerator, thumbnailTemplate, mediator, SecurityContext, router) {
  return BaseRow.extend({
    thumbnailTemplate: _.template(thumbnailTemplate),

    /**
     * Return true if the user can remove the association, false otherwise.
     *
     * The use can remove an association if he has the permission and if the association
     * does not come from inheritance.
     *
     * @return {Boolean}
     */
    canRemoveAssociation() {
      const permissionGranted = SecurityContext.isGranted('pim_enrich_associations_remove');
      const fromInheritance = this.model.get('from_inheritance');

      return permissionGranted && !fromInheritance;
    },

    /**
     * {@inheritdoc}
     */
    getTemplateOptions() {
      const isProductModel = this.isProductModel();
      const id = this.model.id.replace(/product-model-|product-/g, '');
      const label = this.model.get('label') || `[${id}]`;
      const canRemoveAssociation = this.canRemoveAssociation();

      return {
        useLayerStyle: isProductModel,
        label,
        identifier: this.model.get('identifier') ?? `[${id}]`,
        imagePath: this.getThumbnailImagePath(),
        canRemoveAssociation,
        redirectUrl: router.generate(
          this.isProductModel() ? 'pim_enrich_product_model_edit' : 'pim_enrich_product_edit',
          this.isProductModel() ? {id} : {uuid: id}
        ),
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
        mediator.trigger('datagrid:unselectModel:association-product-model-grid', this.model);
        row.remove();
      });
    },

    /**
     * {@inheritdoc}
     */
    getRenderableColumns() {
      return [this.getCompletenessCellType()];
    },
  });
});
