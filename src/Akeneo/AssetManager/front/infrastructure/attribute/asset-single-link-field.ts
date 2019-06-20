const BaseField = require('pim/form/common/fields/field');
import * as $ from 'jquery';
import assetFamilyFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset-family';
import AssetFamilyListItem from 'akeneoassetmanager/domain/model/asset-family/list';
const __ = require('oro/translator');
const _ = require('underscore');
const UserContext = require('pim/user-context');
const template = _.template(require('pim/template/form/common/fields/select'));

/**
 * Asset family field for attribute form
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetFamilyField extends (BaseField as {new (config: any): any}) {
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

    assetFamilyFetcher.fetchAll().then((assetFamilies: AssetFamilyListItem[]) => {
      this.assetFamilies = assetFamilies;
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
        defaultLabel: __('pim_enrich.entity.attribute.property.asset_family.default_label'),
      },
    });
  }

  getChoices() {
    return this.assetFamilies.reduce(
      (result: {[key: string]: string}, assetFamily: AssetFamilyListItem) => {
        result[assetFamily.getIdentifier().stringValue()] = assetFamily.getLabel(
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

export = AssetFamilyField;
