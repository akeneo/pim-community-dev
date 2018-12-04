/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

import recordFetcher from 'akeneoreferenceentity/infrastructure/fetcher/record';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';

define([
  'pim/form/common/fields/simple-select-async',
  'pim/user-context',
  'pim/form/common/fields/values/values-behavior',
  'routing',
  'pim/i18n',
  'oro/translator',
  'pim/fetcher-registry',
  'pim/initselect2',
  'akeneoreferenceentity/infrastructure/fetcher/record',
], (BaseField, UserContext, ValuesBehavior, Routing, i18n, __, fetcherRegistry, initSelect2, recordFetcher) => {
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
              const selectedRecords = [];
              const searchQuery = {
                channel: UserContext.get('catalogScope'),
                locale: UserContext.get('catalogLocale'),
                size: this.resultsPerPage,
                page: page - 1,
                filters: [
                  {
                    field: 'reference_entity',
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
                    value: selectedRecords,
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
              recordFetcher.default
                .fetch(ReferenceEntityIdentifier.create(attribute.reference_data_name), RecordCode.create(initialValue))
                .then(record => {
                  callback(this.formatItem(record.normalize()));
                });
            },
            multiple: false,
            placeholder: __('pim_reference_entity.record.selector.no_value'),
            placeholderOption: '',
          };
        });
    },

    getChoiceUrl: function(attribute) {
      return Routing.generate(this.config.url, {referenceEntityIdentifier: attribute.reference_data_name});
    },

    formatItem: normalizedRecord => {
      return {
        id: normalizedRecord.code,
        text: i18n.getLabel(normalizedRecord.labels, UserContext.get('catalogLocale'), normalizedRecord.code),
        original: normalizedRecord,
      };
    },
  });
});
