const BaseField = require('pim/form/common/fields/field');
import $ from 'jquery';
import referenceEntityFetcher from 'akeneoreferenceentity/infrastructure/fetcher/reference-entity';
import ReferenceEntityListItem from 'akeneoreferenceentity/domain/model/reference-entity/list';
const __ = require('oro/translator');
const _ = require('underscore');
const UserContext = require('pim/user-context');
const Property = require('pim/common/property');
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
        this.getRoot().trigger('pim_enrich:form:form-tabs:remove-errors');
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
    this.referenceEntities = [];
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
    const formDataValue = Property.accessProperty(this.getFormData(), this.fieldName);
    const value = this.referenceEntities.find(
      (referenceEntity: ReferenceEntityListItem) =>
        referenceEntity
          .getIdentifier()
          .stringValue()
          .toLowerCase() === formDataValue?.toLowerCase()
    );

    return template({
      ...templateContext,
      value: value?.getIdentifier().stringValue(),
      choices: this.getChoices(),
      multiple: false,
      readOnly: this.isReadOnly(),
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

  /**
   * {@inheritdoc}
   */
  isReadOnly() {
    if (undefined !== this.config.readOnly) {
      return this.config.readOnly;
    }

    return undefined !== this.getFormData()?.meta?.id;
  }

  /**
   * {@inheritdoc}
   */
  protected getFieldErrors(errors: any) {
    if (Array.isArray(errors)) {
      return BaseField.prototype.getFieldErrors.apply(this, arguments);
    }

    const error = Property.accessProperty(errors, this.fieldName, null);
    if (error === null) {
      return [];
    } else {
      return [{message: error}];
    }
  }
}

export = ReferenceEntityField;
