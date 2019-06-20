import * as React from 'react';
import * as ReactDOM from 'react-dom';
import AssetSelector from 'akeneoassetmanager/application/component/app/asset-selector';
import {createIdentifier as createAssetFamilyIdentifier} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AssetCode, {createCode as createAssetCode} from 'akeneoassetmanager/domain/model/asset/code';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import __ from 'akeneoassetmanager/tools/translator';

const Field = require('pim/field');
const UserContext = require('pim/user-context');

/**
 * Asset family field for attribute form
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetFamilyField extends (Field as {new (config: any): any}) {
  constructor(config: any) {
    super(config);

    this.fieldType = 'akeneo-asset-single-link-field';
  }

  renderInput(templateContext: any) {
    const container = document.createElement('div');
    let valueData = null;
    if (null !== templateContext.value.data) {
      valueData = createAssetCode(templateContext.value.data);
    }

    ReactDOM.render(
      <AssetSelector
        assetFamilyIdentifier={createAssetFamilyIdentifier(templateContext.attribute.reference_data_name)}
        value={valueData}
        locale={LocaleReference.create(UserContext.get('catalogLocale'))}
        channel={ChannelReference.create(UserContext.get('catalogScope'))}
        multiple={false}
        readOnly={'view' === templateContext.editMode}
        placeholder={__('pim_asset_manager.asset.selector.no_value')}
        onChange={(assetCode: AssetCode) => {
          this.errors = [];
          this.setCurrentValue(
            null !== assetCode && '' !== assetCode.stringValue() ? assetCode.stringValue() : null
          );
          this.render();
        }}
      />,
      container
    );
    return container;
  }
}

module.exports = AssetFamilyField;
