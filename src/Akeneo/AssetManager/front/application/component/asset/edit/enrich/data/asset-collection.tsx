import * as React from 'react';
import Value from 'akeneoassetmanager/domain/model/asset/value';
import AssetSelector from 'akeneoassetmanager/application/component/app/asset-selector';
import {AssetAttribute} from 'akeneoassetmanager/domain/model/attribute/type/asset';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import __ from 'akeneoassetmanager/tools/translator';
import AssetCollectionData from 'akeneoassetmanager/domain/model/asset/data/asset-collection';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';

const View = ({
  value,
  onChange,
  channel,
  locale,
  canEditData,
}: {
  value: Value;
  channel: ChannelReference;
  locale: LocaleReference;
  onChange: (value: Value) => void;
  canEditData: boolean;
}) => {
  if (!(value.data instanceof AssetCollectionData)) {
    return null;
  }

  const attribute = value.attribute as AssetAttribute;

  return (
    //The first children of a FieldContainer will stretch to the full width if not contained in a div.
    //I didn't found a better way to fix it. So we need this class
    <div className="asset-selector-container">
      <AssetSelector
        id={`pim_asset_manager.asset.enrich.${value.attribute.getCode()}`}
        value={value.data.assetCollectionData}
        multiple={true}
        locale={locale}
        channel={channel}
        placeholder={__('pim_asset_manager.asset.selector.no_value')}
        assetFamilyIdentifier={attribute.assetType.getAssetFamilyIdentifier()}
        readOnly={!canEditData}
        onChange={(assetCodes: AssetCode[]) => {
          if (canEditData) {
            const newData = AssetCollectionData.create(assetCodes);
            const newValue = value.setData(newData);

            onChange(newValue);
          }
        }}
      />
    </div>
  );
};

export const view = View;
