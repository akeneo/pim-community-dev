import {getErrorsForPath} from '@akeneo-pim-community/shared';
import {AssetFamilyField, AssetFamilyFieldProps} from '../AssetFamilyField';
const BaseField = require('pim/form/common/fields/field');

class AssetFamilyFieldView extends BaseField {
  renderInput() {
    return '';
  }

  renderExtensions() {
    const data = this.getFormData();

    const onChange = (assetFamilyIdentifier: string) => {
      this.setData({...data, configuration: {...data.configuration, asset_family_identifier: assetFamilyIdentifier}});
      this.render();
    };

    const props: AssetFamilyFieldProps = {
      assetFamilyIdentifier: data.configuration.asset_family_identifier ?? null,
      readOnly: this.config.readOnly ?? false,
      onChange,
    };

    this.renderReact(AssetFamilyField, props, this.$('.AknFieldContainer-inputContainer')[0]);

    return this;
  }

  /**
   * {@inheritdoc}
   */
  protected getFieldErrors(errors: any) {
    return getErrorsForPath(errors.normalized_errors, '[asset_family_identifier]').map(({messageTemplate}) => ({
      message: messageTemplate,
    }));
  }
}

export = AssetFamilyFieldView;
