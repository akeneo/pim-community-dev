import * as $ from 'jquery';
import recordFetcher from 'akeneoreferenceentity/infrastructure/fetcher/record';
import Record, {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import hydrateAll from 'akeneoreferenceentity/application/hydrator/hydrator';
import hydrator from 'akeneoreferenceentity/application/hydrator/record';

const Field = require('pim/field');
const _ = require('underscore');
const UserContext = require('pim/user-context');
const template = _.template(require('pim/template/form/common/fields/select'));

const extendTemplateContext = (templateContext: any, records: Record[]) => {
  templateContext.choices = records.reduce(
    (choices: {[key: string]: string}, record: Record) => ({
      ...choices,
      [record.getCode().stringValue()]: record.getLabel(UserContext.get('catalog_default_locale')),
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
 * Reference entity field for attribute form
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceEntityField extends (Field as {new (config: any): any}) {
  constructor(config: any) {
    super(config);

    this.fieldType = 'akeneo-reference-entity-field';
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
          channel: templateContext.channel,
          size: 25,
          page: 0,
          filters: [
            {
              value: templateContext.attribute.reference_data_name,
              field: 'reference_entity',
              operator: '=',
              context: {},
            },
          ],
        })
        .then(({items}: {items: NormalizedRecord[]}) => {
          promise.resolve(extendTemplateContext(templateContext, hydrateAll<Record>(hydrator)(items)));
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

module.exports = ReferenceEntityField;
