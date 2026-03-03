'use strict';

define([
  'oro/translator',
  'pim/controller/front',
  'pim/form-builder',
  'pim/fetcher-registry',
  'pim/user-context',
  'pim/dialog',
  'pim/page-title',
  'pim/i18n',
], function (__, BaseController, FormBuilder, FetcherRegistry, UserContext, Dialog, PageTitle, i18n) {
  return BaseController.extend({
    initialize: function () {
      this.config = __moduleConfig;
    },

    /**
     * {@inheritdoc}
     */
    renderForm: function (route) {
      return FetcherRegistry.getFetcher(this.config.fetcher)
        .fetch(route.params.code, {cached: false})
        .then(group => {
          if (!this.active) {
            return;
          }

          PageTitle.set({'group.label': i18n.getLabel(group.labels, UserContext.get('catalogLocale'), group.code)});

          return FormBuilder.build(group.meta.form).then(form => {
            this.on('pim:controller:can-leave', function (event) {
              form.trigger('pim_enrich:form:can-leave', event);
            });
            form.setData(group);
            form.trigger('pim_enrich:form:entity:post_fetch', group);
            form.setElement(this.$el).render();

            return form;
          });
        });
    },
  });
});
