'use strict';

define([
  'jquery',
  'underscore',
  'oro/translator',
  'pim/form',
  'pimee/template/product/publish',
  'oro/loading-mask',
  'pim/fetcher-registry',
  'pimee/published-product-manager',
  'pim/router',
  'oro/messenger',
  'pim/dialog',
], function(
  $,
  _,
  __,
  BaseForm,
  template,
  LoadingMask,
  FetcherRegistry,
  PublishedProductManager,
  router,
  messenger,
  Dialog
) {
  return BaseForm.extend({
    template: _.template(template),
    events: {
      'click .publish-product:not(.disabled)': 'publish',
      'click .unpublish-product': 'unpublish',
    },
    configure: function() {
      this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);

      return BaseForm.prototype.configure.apply(this, arguments);
    },
    render: function() {
      if (!this.getFormData().meta.is_owner) {
        return this.remove();
      }

      this.$el.html(
        this.template({
          product: this.getFormData(),
        })
      );
      this.delegateEvents();

      return this;
    },
    publish: function() {
      Dialog.confirm(
        __('pimee_enrich.entity.product.module.publish.content'),
        __('pimee_enrich.entity.product.module.publish.title'),
        this.doPublish.bind(this),
        __('pim_enrich.entity.product.plural_label')
      );
    },
    unpublish: function() {
      Dialog.confirm(
        __('pimee_enrich.entity.product.module.unpublish.content'),
        __('pimee_enrich.entity.product.module.unpublish.title'),
        this.doUnpublish.bind(this),
        __('pim_menu.item.published_product')
      );
    },
    doPublish: function() {
      this.togglePublished(true);
    },
    doUnpublish: function() {
      this.togglePublished(false);
    },
    togglePublished: function(publish) {
      var productUuid = this.getProductUuid();
      var loadingMask = new LoadingMask();
      loadingMask
        .render()
        .$el.appendTo(this.getRoot().$el)
        .show();

      var method = publish ? PublishedProductManager.publish : PublishedProductManager.unpublish;

      // TODO: We shouldn't force product fetching, we should use request response (cf. send for approval)
      return method(productUuid)
        .done(
          function() {
            FetcherRegistry.getFetcher('product')
              .fetch(this.getFormData().meta.id)
              .done(
                function(product) {
                  loadingMask.hide().$el.remove();
                  messenger.notify(
                    'success',
                    __(
                      publish
                        ? 'pimee_enrich.entity.published_product.flash.publish.success'
                        : 'pimee_enrich.entity.published_product.flash.unpublish.success'
                    )
                  );

                  this.setData(product);

                  this.getRoot().trigger('pim_enrich:form:entity:post_fetch', product);
                  this.getRoot().trigger('pim_enrich:form:entity:post_publish', product);
                }.bind(this)
              );
          }.bind(this)
        )
        .fail(function(response) {
          if (!publish) {
            messenger.notify('error', __('pimee_enrich.entity.published_product.flash.unpublish.fail'));
          } else if (response.responseJSON && response.responseJSON.message) {
            messenger.notify('error', __(response.responseJSON.message));
          } else {
            messenger.notify('error', __('pimee_enrich.entity.published_product.flash.publish.fail'));
          }
        })
        .always(function() {
          loadingMask.hide().$el.remove();
        });
    },
    getProductUuid: function() {
      return this.getFormData().meta.id;
    },
  });
});
