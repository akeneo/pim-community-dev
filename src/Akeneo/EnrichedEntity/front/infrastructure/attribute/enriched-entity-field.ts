const BaseField = require('pim/form/common/fields/field');
import * as $ from 'jquery';
import enrichedEntityFetcher from 'akeneoenrichedentity/infrastructure/fetcher/enriched-entity';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
const __ = require('oro/translator');
const _ = require('underscore');
const UserContext = require('pim/user-context');
const template = _.template(require('pim/template/form/common/fields/select'));

/**
 * Enriched entity field for attribute form
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EnrichedEntityField extends (BaseField as {new (config: any): any}) {
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

    enrichedEntityFetcher.fetchAll().then((enrichedEntities: EnrichedEntity[]) => {
      this.enrichedEntities = enrichedEntities;
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
        defaultLabel: __('pim_enrich.entity.attribute.property.enriched_entity.default_label'),
      },
    });
  }

  getChoices() {
    return this.enrichedEntities.reduce((result: {[key: string]: string}, enrichedEntity: EnrichedEntity) => {
      result[enrichedEntity.getIdentifier().stringValue()] = enrichedEntity.getLabel(UserContext.get('catalogLocale'));

      return result;
    }, {});
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

export = EnrichedEntityField;
