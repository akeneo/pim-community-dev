const BaseField = require('pim/form/common/fields/field');
import * as $ from 'jquery';
import referenceEntityFetcher from 'akeneoreferenceentity/infrastructure/fetcher/reference-entity';
import ReferenceEntityListItem from 'akeneoreferenceentity/domain/model/reference-entity/list';
const __ = require('oro/translator');
const _ = require('underscore');
const UserContext = require('pim/user-context');
const template = _.template(require('pim/template/form/common/fields/select'));

/**
 * Reference entity field for attribute form
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceEntityField extends (BaseField as {new (config: any): any}) {
  constructor(config: any) {
    super(config);

    this.events = {
      'change select': function(event: any) {
        this.errors = [];
        this.updateModel(this.getFieldValue(event.target));
        this.getRoot().render();
      },
    };
  }

  /**
   * {@inheritdoc}
   */
  configure() {
    const promise = $.Deferred();

    referenceEntityFetcher.fetchAll().then((referenceEntities: ReferenceEntityListItem[]) => {
      this.referenceEntities = referenceEntities;
      promise.resolve();
    });

    return $.when(BaseField.prototype.configure.apply(this, arguments), promise.promise());
  }

  /**
   * {@inheritdoc}
   */
  renderInput(templateContext: any) {
    return template({
      ...templateContext,
      value: this.getFormData()[this.fieldName],
      choices: this.getChoices(),
      multiple: false,
      readOnly: undefined !== this.getFormData().meta,
      labels: {
        defaultLabel: __('pim_enrich.entity.attribute.property.reference_entity.default_label'),
      },
    });
  }

  getChoices() {
    return this.referenceEntities.reduce(
      (result: {[key: string]: string}, referenceEntity: ReferenceEntityListItem) => {
        result[referenceEntity.getIdentifier().stringValue()] = referenceEntity.getLabel(
          UserContext.get('catalogLocale')
        );

        return result;
      },
      {}
    );
  }

  /**
   * {@inheritdoc}
   */
  postRender() {
    this.$('select.select2').select2({allowClear: true});
  }

  /**
   * {@inheritdoc}
   */
  getFieldValue(field: any) {
    return $(field).val();
  }
}

export = ReferenceEntityField;
