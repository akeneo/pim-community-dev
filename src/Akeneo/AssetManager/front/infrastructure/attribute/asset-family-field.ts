import {AssetFamilyField, AssetFamilyFieldProps} from '../AssetFamilyField';
const BaseField = require('pim/form/common/fields/field');

class AssetFamilyFieldView extends BaseField {
  renderInput() {
    return '';
  }

  renderExtensions() {
    const data = this.getFormData();

    const onChange = (assetFamilyIdentifier: string) => {
      this.setData({...data, reference_data_name: assetFamilyIdentifier});
      this.render();
    };

    const props: AssetFamilyFieldProps = {
      assetFamilyIdentifier: data.reference_data_name ?? null,
      readOnly: undefined !== data.meta,
      onChange,
    };

    this.renderReact(AssetFamilyField, props, this.$('.AknFieldContainer-inputContainer')[0]);

    return this;
  }
}

export = AssetFamilyFieldView;
