/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define(['underscore', 'pim/controller/front', 'pim/form-builder', 'pim/fetcher-registry'], function (
  _,
  BaseController,
  FormBuilder,
  fetcherRegistry
) {
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

      var code = this.getQueryParam(location.href, 'code');
      var type = this.getQueryParam(location.href, 'attribute_type');

      return FormBuilder.getFormMeta('pim-attribute-create-form')
        .then(FormBuilder.buildForm)
        .then(form => {
          if (code) {
            form.setCode(code);
          }
          form.setType(type);

          return form.configure().then(() => {
            return form;
          });
        })
        .then(form => {
          this.on('pim:controller:can-leave', event => {
            form.trigger('pim_enrich:form:can-leave', event);
          });

          form.setData(this.getNewAttribute(type, code));

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
      if (-1 === url.lastIndexOf('?')) {
        return '';
      }
      var params = url.substring(url.lastIndexOf('?') + 1);
      if (!params) {
        return null;
      }

      var stringParams = params.split('&');
      var objectParams = {};
      stringParams.forEach(function (stringParam) {
        var tab = stringParam.split('=');
        if (tab.length === 2) {
          objectParams[tab[0]] = tab[1];
        }
      });

      return objectParams[paramName] ?? '';
    },

    /**
     * @param {String} type
     *
     * @return {Object}
     */
    getNewAttribute: function (type, code) {
      return {
        code: code ?? '',
        labels: {},
        type: type,
        available_locales: [],
      };
    },
  });
});
