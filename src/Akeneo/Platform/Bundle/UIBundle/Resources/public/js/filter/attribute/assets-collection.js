'use strict';

define([
  'jquery',
  'underscore',
  'oro/translator',
  'routing',
  'pim/filter/attribute/select',
  'pim/fetcher-registry',
  'pim/user-context',
  'pim/i18n',
  'pim/router',
  'jquery.select2',
], function($, _, __, Routing, SelectFilter, FetcherRegistry, UserContext, i18n, router) {
  return SelectFilter.extend({
    resultsPerPage: 20,

    /**
     * {@inheritdoc}
     */
    getSelect2Options: function(attribute) {
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
        initSelection: async (element, callback) => {
          const initialValues = element.val().split(',');
          const initQuery = {
            channel: UserContext.get('catalogScope'),
            locale: UserContext.get('catalogLocale'),
            size: 200,
            page: 0,
            filters: [
              {
                field: 'asset_family',
                operator: '=',
                value: attribute.reference_data_name,
              },
              {
                field: 'code',
                operator: 'IN',
                value: initialValues,
              },
            ],
          };

          const route = router.generate('akeneo_asset_manager_asset_index_rest', {
            assetFamilyIdentifier: attribute.reference_data_name,
          });

          const response = await fetch(route, {
            method: 'PUT',
            body: JSON.stringify(initQuery),
            headers: {
              'Content-Type': 'application/json',
            },
          });

          const result = await response.json();

          callback(result.items.map(this.formatItem.bind(this)));
        },
        multiple: true,
      };
    },

    formatItem: normalizedAsset => {
      return {
        id: normalizedAsset.code,
        text: i18n.getLabel(normalizedAsset.labels, UserContext.get('catalogLocale'), normalizedAsset.code),
        original: normalizedAsset,
      };
    },

    /**
     * {@inheritdoc}
     */
    getType: function() {
      return 'akeneo-attribute-select-reference-data-filter';
    },

    /**
     * Clean invalid values by removing possibly non-existent options coming from database.
     * This method returns a promise which, once resolved, should return the attribute.
     *
     * @returns {Promise}
     */
    cleanInvalidValues: async function(attribute, currentValues) {
      if (undefined === currentValues || 0 === currentValues.length) {
        return [];
      }

      const query = {
        channel: UserContext.get('catalogScope'),
        locale: UserContext.get('catalogLocale'),
        size: 200,
        page: 0,
        filters: [
          {
            field: 'asset_family',
            operator: '=',
            value: attribute.reference_data_name,
          },
          {
            field: 'code',
            operator: 'IN',
            value: currentValues,
          },
        ],
      };

      const route = router.generate('akeneo_asset_manager_asset_index_rest', {
        assetFamilyIdentifier: attribute.reference_data_name,
      });
      const response = await fetch(route, {
        method: 'PUT',
        body: JSON.stringify(query),
        headers: {
          'Content-Type': 'application/json',
        },
      });

      const result = await response.json();

      return result.items.map(normalizedAsset => normalizedAsset.code);
    },

    getChoiceUrl: function getChoiceUrl(attribute) {
      return Routing.generate(this.config.url, {assetFamilyIdentifier: attribute.reference_data_name});
    },

    getChoices: function getChoices(attribute) {
      const ajaxConfig = {
        url: this.getChoiceUrl(attribute),
        quietMillis: 250,
        cache: true,
        method: 'PUT',
        params: {contentType: 'application/json;charset=utf-8'},
        data: (() => {
          const searchQuery = {
            channel: UserContext.get('catalogScope'),
            locale: UserContext.get('catalogLocale'),
            size: this.resultsPerPage,
            page: 0,
            filters: [
              {
                field: 'asset_family',
                operator: '=',
                value: attribute.reference_data_name,
              },
            ],
          };

          return JSON.stringify(searchQuery);
        })(),
      };

      if (null === this.choicePromise) {
        this.choicePromise = $.ajax(ajaxConfig);
      }

      return this.choicePromise;
    },
  });
});
