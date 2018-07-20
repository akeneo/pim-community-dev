import * as $ from 'jquery';
import recordFetcher from 'akeneoenrichedentity/infrastructure/fetcher/record';
import Record from 'akeneoenrichedentity/domain/model/record/record';
const Field = require('pim/field');
const _ = require('underscore');
const UserContext = require('pim/user-context');
const template = _.template(require('pim/template/form/common/fields/select'));

const extendTemplateContext = (templateContext: any, records: Record[]) => {
  templateContext.choices = records.reduce(
    (choices: {[key: string]: string}, record: Record) => ({
      ...choices,
      [record.getIdentifier().stringValue()]: record.getLabel(UserContext.get('catalogLocale')),
    }),
    {}
  );
  templateContext.multiple = true;
  templateContext.readOnly = false;
  templateContext.fieldName = templateContext.attribute.reference_data_name;
  templateContext.labels = {};
  templateContext.value = templateContext.value.data;

  return templateContext;
};

/**
 * Enriched entity field for attribute form
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EnrichedEntityField extends (Field as {new (config: any): any}) {
  constructor(config: any) {
    super(config);

    this.fieldType = 'akeneo-enriched-entity-field';
    this.events = {
      'change select': (event: any) => {
        this.errors = [];
        this.setCurrentValue(this.getFieldValue(event.target));
      },
    };
  }

  getTemplateContext() {
    return super.getTemplateContext().then(function(templateContext: any) {
      const promise = $.Deferred();

      recordFetcher
        .search({
          locale: templateContext.locale,
          limit: 25,
          page: 0,
          filters: [
            {
              value: templateContext.attribute.reference_data_name,
              field: 'enriched_entity',
              operator: '=',
              context: {},
            },
          ],
        })
        .then(({items}: {items: Record[]}) => {
          promise.resolve(extendTemplateContext(templateContext, items));
        });

      return promise.promise();
    });
  }

  renderInput(templateContext: any) {
    return template({...templateContext});
  }

  /**
   * {@inheritdoc}
   */
  postRender() {
    this.$('select.select2').select2({allowClear: true});
  }

  getFieldValue(field: any) {
    const value = $(field).val();

    return null === value ? [] : value;
  }
}

module.exports = EnrichedEntityField;
