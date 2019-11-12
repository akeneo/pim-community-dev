import * as React from 'react';
import Value from 'akeneoassetmanager/domain/model/asset/value';
import AssetData, {create} from 'akeneoassetmanager/domain/model/asset/data/asset';
import AssetSelector from 'akeneoassetmanager/application/component/app/asset-selector';
import {AssetAttribute} from 'akeneoassetmanager/domain/model/attribute/type/asset';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import __ from 'akeneoassetmanager/tools/translator';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import {assetTypeIsEmpty} from 'akeneoassetmanager/domain/model/attribute/type/asset/asset-type';

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
  const attribute = value.attribute as AssetAttribute;
  if (!(value.data instanceof AssetData) || assetTypeIsEmpty(attribute.assetType)) {
    return null;
  }

  return (
    <div className="asset-selector-container">
      <AssetSelector
        id={`pim_asset_manager.asset.enrich.${value.attribute.getCode()}`}
        value={value.data.assetData}
        locale={locale}
        channel={channel}
        placeholder={__('pim_asset_manager.asset.selector.no_value')}
        assetFamilyIdentifier={attribute.assetType}
        readOnly={!canEditData}
        onChange={(assetCode: AssetCode) => {
          if (canEditData) {
            const newData = create(assetCode);
            const newValue = value.setData(newData);

            onChange(newValue);
          }
        }}
      />
    </div>
  );
};

export const view = View;
