'use strict';

define(['underscore', 'pim/simple-select-field', 'routing', 'pim/fetcher-registry'], function(
  _,
  SimpleselectField,
  Routing,
  FetcherRegistry
) {
  return SimpleselectField.extend({
    fieldType: 'reference-simple-select',
    getTemplateContext: function() {
      return SimpleselectField.prototype.getTemplateContext.apply(this, arguments).then(function(templateContext) {
        templateContext.userCanAddOption = false;

        return templateContext;
      });
    },
    getChoiceUrl: function() {
      return FetcherRegistry.getFetcher('reference-data-configuration')
        .fetchAll()
        .then(
          _.bind(function(config) {
            return Routing.generate('pim_ui_ajaxentity_list', {
              class: config[this.attribute.reference_data_name].class,
              dataLocale: this.context.locale,
              collectionId: this.attribute.meta.id,
              options: {type: 'code'},
            });
          }, this)
        );
    },
  });
});
