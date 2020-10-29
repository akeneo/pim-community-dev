'use strict';

define([
  'jquery',
  'underscore',
  'oro/translator',
  'pim/form',
  'pim/fetcher-registry',
  'pimee/template/product/tab/history/revert',
  'pim/router',
  'oro/messenger',
  'oro/loading-mask',
  'pim/dialog',
], function($, _, __, BaseForm, FetcherRegistry, revertTemplate, router, messenger, LoadingMask, Dialog) {
  return BaseForm.extend({
    template: _.template(revertTemplate),

    /**
     * Trigger a new event 'action:register' to send the revert action button to the parent.
     *
     * {@inheritdoc}
     */
    configure: function() {
      var $revertAction = $(this.template());
      $revertAction.on('click', this.revert.bind(this));

      this.trigger('action:register', {
        code: 'revert',
        element: $revertAction,
      });

      return BaseForm.prototype.configure.apply(this, arguments);
    },

    /**
     * Revert the product to the specified version
     *
     * @param {Event} event
     */
    revert: function(event) {
      event.stopPropagation();

      Dialog.confirm(
        __('pimee_enrich.entity.product.module.revert.content'),
        __('pimee_enrich.entity.product.module.revert.title'),
        function() {
          var loadingMask = new LoadingMask();
          loadingMask
            .render()
            .$el.appendTo(this.getRoot().$el)
            .show();

          $.get(
            router.generate('pimee_revert_revert_product', {
              id: $(event.currentTarget)
                .parents('.entity-version')
                .data('version-id'),
            })
          )
            .done(
              function() {
                // TODO: We shouldn't force product fetching,
                // we should use request response (cf. send for approval)
                FetcherRegistry.getFetcher('product')
                  .fetch(this.getFormData().meta.id)
                  .done(
                    function(product) {
                      loadingMask.hide().$el.remove();
                      messenger.notify('success', __('pimee_enrich.entity.published_product.flash.revert.success'));

                      this.setData(product);

                      this.getRoot().trigger('pim_enrich:form:entity:post_fetch', product);
                      this.getRoot().trigger('pim_enrich:form:entity:post_revert', product);
                    }.bind(this)
                  );
              }.bind(this)
            )
            .fail(
              function(response) {
                loadingMask.hide().$el.remove();
                const jsonResponse = response.responseJSON
                  ? response.responseJSON
                  : {error: __('pim_enrich.entity.fallback.generic_error')};

                if (Array.isArray(jsonResponse)) {
                  this.formatParameters(jsonResponse).forEach(function(error) {
                    messenger.notify('error', __(error.messageTemplate, error.parameters));
                  });
                } else {
                  messenger.notify('error', jsonResponse.error);
                }
              }.bind(this)
            );
        }.bind(this),
        null,
        'AknButton--apply',
        null,
        'products'
      );
    },
    formatParameters: function(errors) {
      return errors.map(error => ({
        ...error,
        parameters: Object.keys(error.parameters).reduce(
          (result, key) => ({
            ...result,
            [key.replace('{{ ', '').replace(' }}', '')]: error.parameters[key],
          }),
          {}
        ),
      }));
    },
  });
});
