'use strict';

define([
  'jquery',
  'underscore',
  'oro/translator',
  'backbone',
  'pim/form',
  'pim/user-context',
  'pimee/rule-manager',
  'pim/router',
  'pim/security-context',
  'pimee/template/product/tab/attribute/smart-attribute',
], function($, _, __, Backbone, BaseForm, UserContext, RuleManager, Routing, SecurityContext, smartAttributeTemplate) {
  return BaseForm.extend({
    template: _.template(smartAttributeTemplate),
    configure: function() {
      this.listenTo(this.getRoot(), 'pim_enrich:form:field:extension:add', this.addFieldExtension);
    },
    addFieldExtension: function(event) {
      let attributeCodes = Object.keys(this.getFormData().values);
      if (this.getFormData().meta.model_type === 'product_model') {
        this.getFormData().meta.family_variant.variant_attribute_sets.forEach(attributeSets => {
          attributeCodes = [...attributeCodes, ...attributeSets.attributes];
        });
      }
      event.promises.push(
        RuleManager.getFamilyAttributesRulesNumber(attributeCodes).done(attributesRulesNumber => {
          const deferred = $.Deferred();
          const field = event.field;
          const fieldAttributeCode = field.attribute.code;
          const attributeHasRules = Object.keys(attributesRulesNumber).includes(fieldAttributeCode);
          if (attributeHasRules) {
            const translation = SecurityContext.isGranted('pimee_catalog_rule_rule_view_permissions')
              ? 'pimee_enrich.entity.product.module.attribute.can_be_updated_by_rules'
              : 'pimee_enrich.entity.product.module.attribute.can_be_updated_by_rules_readonly';
            const element = this.template({
              __,
              translation,
              rules_number: attributesRulesNumber[fieldAttributeCode],
            });
            const $element = $(element);
            $element.find('span').on('click', () => {
              sessionStorage.setItem('current_form_tab', 'pim-attribute-edit-form-rules-tab');
              Routing.redirectToRoute('pim_enrich_attribute_edit', {code: fieldAttributeCode});
            });
            field.addElement('footer', 'from_smart', $element);
          }
          deferred.resolve();

          return deferred.promise();
        })
      );

      return this;
    },
  });
});
