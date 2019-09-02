/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

import assetFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';

define([
  'pim/form/common/fields/simple-select-async',
  'pim/user-context',
  'pim/form/common/fields/values/values-behavior',
  'routing',
  'pim/i18n',
  'oro/translator',
  'pim/fetcher-registry',
  'pim/initselect2',
], (BaseField, UserContext, ValuesBehavior, Routing, i18n, __, fetcherRegistry, initSelect2) => {
  return BaseField.extend({
    resultsPerPage: 20,

    /**
     * {@inheritdoc}
     */
    updateModel(value) {
      ValuesBehavior.writeValue.call(this, BaseField, value);
    },

    /**
     * {@inheritdoc}
     */
    getModelValue() {
      return ValuesBehavior.readValue.call(this, BaseField);
    },

    /**
     * {@inheritdoc}
     */
    postRender(templateContext) {
      this.getSelect2Options(templateContext).then(select2Configuration => {
        initSelect2.init(this.$('.select2'), select2Configuration);
      });
    },

    /**
     * Returns the options for Select2 library
     *
     * @returns {Object}
     */
    getSelect2Options(templateContext) {
      const attributeCode = templateContext.fieldName.substring('values.'.length);

      return fetcherRegistry
        .getFetcher('attribute')
        .fetch(attributeCode)
        .then(attribute => {
          const ajaxConfig = {
            url: this.getChoiceUrl(attribute),
            quietMillis: 250,
            cache: true,
            type: 'PUT',
            params: {contentType: 'application/json;charset=utf-8'},
            data: (term, page) => {
              const selectedAssets = [];
              const searchQuery = {
                channel: UserContext.get('catalogScope'),
                locale: UserContext.get('catalogLocale'),
                size: this.resultsPerPage,
                page: page - 1,
                filters: [
                  {
                    field: 'asset_family',
                    operator: '=',
                    value: attribute.reference_data_name,
                  },
                  {
                    field: 'code_label',
                    operator: 'IN',
                    value: term,
                  },
                  {
                    field: 'code',
                    operator: 'NOT IN',
                    value: selectedAssets,
                  },
                ],
              };

              return JSON.stringify(searchQuery);
            },
            results: result => {
              const items = result.items.map(this.formatItem.bind(this));

              return {
                more: this.resultsPerPage === items.length,
                results: items,
              };
            },
          };

          return {
            ajax: ajaxConfig,
            initSelection: (element, callback) => {
              const initialValue = element.val();
              assetFetcher
                .fetch(AssetFamilyIdentifier.create(attribute.reference_data_name), AssetCode.create(initialValue))
                .then(result => {
                  callback(this.formatItem(result.asset.normalize()));
                });
            },
            multiple: false,
            placeholder: __('pim_asset_manager.asset.selector.no_value'),
            placeholderOption: '',
          };
        });
    },

    getChoiceUrl: function(attribute) {
      return Routing.generate(this.config.url, {assetFamilyIdentifier: attribute.reference_data_name});
    },

    formatItem: normalizedAsset => {
      return {
        id: normalizedAsset.code,
        text: i18n.getLabel(normalizedAsset.labels, UserContext.get('catalogLocale'), normalizedAsset.code),
        original: normalizedAsset,
      };
    },
  });
});
