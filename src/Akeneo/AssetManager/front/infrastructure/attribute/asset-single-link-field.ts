const BaseField = require('pim/form/common/fields/field');
import $ from 'jquery';
import {AssetFamilyListItem} from 'akeneoassetmanager/domain/model/asset-family/list';
import {getLabel} from '@akeneo-pim-community/shared';
const __ = require('oro/translator');
const _ = require('underscore');
const UserContext = require('pim/user-context');
const router = require('pim/router');
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
  async configure() {
    const response = await fetch(router.generate('akeneo_asset_manager_asset_family_index_rest'));
    if (response.ok) {
      const backendResponse = await response.json();
      this.assetFamilies = backendResponse.items;
    }

    return BaseField.prototype.configure.apply(this);
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
    return this.assetFamilies.reduce((result: {[key: string]: string}, assetFamily: AssetFamilyListItem) => {
      result[assetFamily.identifier] = getLabel(
        assetFamily.labels,
        UserContext.get('catalogLocale'),
        assetFamily.identifier
      );

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

export = AssetFamilyField;
