/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
  'underscore',
  'pim/controller/front',
  'pim/form-builder',
  'pim/fetcher-registry',
  'pim/user-context',
], function (_, BaseController, FormBuilder, fetcherRegistry, UserContext) {
  return BaseController.extend({
    /**
     * {@inheritdoc}
     */
    renderForm: function () {
      if (!this.active) {
        return;
      }

      fetcherRegistry.getFetcher('attribute-group').clear();
      fetcherRegistry.getFetcher('locale').clear();
      fetcherRegistry.getFetcher('measure').clear();

      const code = this.getQueryParam(location.href, 'code');
      const type = this.getQueryParam(location.href, 'attribute_type');
      const label = this.getQueryParam(location.href, 'label');
      const localizable = this.getQueryParam(location.href, 'localizable');
      const scopable = this.getQueryParam(location.href, 'scopable');
      const unique = this.getQueryParam(location.href, 'unique');
      const labels = {};
      if (label) {
        labels[UserContext.get('catalogLocale')] = label;
      }

      return FormBuilder.getFormMeta('pim-attribute-create-form')
        .then(FormBuilder.buildForm)
        .then(form => {
          if (code) {
            form.setCode(code);
          }
          form.setType(type);
          if (label) {
            form.setLabels(labels);
          }

          if (localizable) {
            form.setLocalizable(localizable);
          }

          if (scopable) {
            form.setScopable(scopable);
          }

          if (unique) {
            form.setUnique(unique);
          }

          return form.configure().then(() => {
            return form;
          });
        })
        .then(form => {
          this.on('pim:controller:can-leave', event => {
            form.trigger('pim_enrich:form:can-leave', event);
          });

          form.setData(this.getNewAttribute(type, code, labels, localizable, scopable, unique));

          form.setElement(this.$el).render();

          return form;
        });
    },

    /**
     * Extracts the value of a given parameter from the query string.
     *
     * @param {String} url
     * @param {String} paramName
     *
     * @return  {String}
     */
    getQueryParam: function (url, paramName) {
      const searchParams = new URL(url.replace('/#/', '/'))?.searchParams;

      return searchParams?.get(paramName);
    },

    /**
     * @param {String} type
     *
     * @return {Object}
     */
    getNewAttribute: function (type, code, labels, localizable, scopable, unique) {
      return {
        code: code ?? '',
        labels: labels,
        type: type,
        localizable: localizable === 'true',
        scopable: scopable === 'true',
        unique: unique === 'true',
        available_locales: [],
      };
    },
  });
});
