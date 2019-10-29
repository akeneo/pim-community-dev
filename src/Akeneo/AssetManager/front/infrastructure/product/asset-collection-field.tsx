import $ from 'jquery';
import * as React from 'react';
import * as ReactDOM from 'react-dom';
import AssetSelector from 'akeneoassetmanager/application/component/app/asset-selector';
import {createIdentifier as createAssetFamilyIdentifier} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AssetCode, {createCode as createAssetCode} from 'akeneoassetmanager/domain/model/asset/code';
import {denormalizeChannelReference} from 'akeneoassetmanager/domain/model/channel-reference';
import __ from 'akeneoassetmanager/tools/translator';
import {denormalizeLocaleReference} from 'akeneoassetmanager/domain/model/locale-reference';

const Field = require('pim/field');
const UserContext = require('pim/user-context');

/**
 * Asset family collection field for attribute form
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetCollectionField extends (Field as {new (config: any): any}) {
  constructor(config: any) {
    super(config);

    this.fieldType = 'pim-asset-collection-field';
  }

  renderInput(templateContext: any) {
    const container = document.createElement('div');
    ReactDOM.render(
      <AssetSelector
        assetFamilyIdentifier={createAssetFamilyIdentifier(templateContext.attribute.reference_data_name)}
        value={templateContext.value.data.map((assetCode: string) => createAssetCode(assetCode))}
        locale={denormalizeLocaleReference(UserContext.get('catalogLocale'))}
        channel={denormalizeChannelReference(UserContext.get('catalogScope'))}
        multiple={true}
        readOnly={'view' === templateContext.editMode}
        placeholder={__('pim_asset_manager.asset.selector.no_value')}
        onChange={(assetCodes: AssetCode[]) => {
          this.errors = [];
          this.setCurrentValue(assetCodes.map((assetCode: AssetCode) => assetCode.stringValue()));
          this.render();
        }}
      />,
      container
    );
    return container;
  }

  getFieldValue(field: any) {
    const value = $(field).val();

    return null === value ? [] : value;
  }
}

module.exports = AssetCollectionField;
