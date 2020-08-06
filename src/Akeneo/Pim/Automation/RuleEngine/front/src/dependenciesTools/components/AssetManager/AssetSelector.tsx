// eslint-disable-next-line @typescript-eslint/no-var-requires
const BaseAssetSelector = require('akeneoassetmanager/application/component/app/asset-selector')
  .default;
import * as React from 'react';

type AssetCode = string;
type AssetFamilyIdentifier = string;
type LocaleReference = string | null;
type ChannelReference = string | null;

export type AssetSelectorProps = {
  value: AssetCode[] | AssetCode | null;
  assetFamilyIdentifier: AssetFamilyIdentifier;
  multiple?: boolean;
  readOnly?: boolean;
  compact?: boolean;
  id?: string;
  locale: LocaleReference;
  channel: ChannelReference;
  placeholder?: string;
  onChange: (value: AssetCode[] | AssetCode | null) => void;
};

export const AssetSelector: React.FC<AssetSelectorProps> = ({
  value,
  assetFamilyIdentifier,
  multiple,
  readOnly,
  compact,
  id,
  locale,
  channel,
  placeholder,
  onChange,
}) => {
  return (
    <BaseAssetSelector
      value={value}
      assetFamilyIdentifier={assetFamilyIdentifier}
      multiple={multiple}
      readOnly={readOnly}
      compact={compact}
      id={id}
      locale={locale}
      channel={channel}
      placeholder={placeholder}
      onChange={onChange}
    />
  );
};
