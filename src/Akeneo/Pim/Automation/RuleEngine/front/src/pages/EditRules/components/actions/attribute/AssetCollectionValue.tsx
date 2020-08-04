import React from 'react';
import {
  useUserCatalogLocale,
  useUserCatalogScope,
} from '../../../../../dependenciesTools/hooks';
import { InputValueProps } from './AttributeValue';
import { getAttributeLabel } from '../../../../../models';
import { Label } from '../../../../../components/Labels';
import { AssetSelector } from '../../../../../dependenciesTools/AssetManager/AssetSelector';

export const parseAssetCollectionValue = (value: any) => {
  if (value === '') {
    return [];
  }
  return value;
};

const AssetCollectionValue: React.FC<InputValueProps> = ({
  attribute,
  value,
  label,
  onChange,
}) => {
  const currentCatalogLocale = useUserCatalogLocale();
  const currentCatalogScope = useUserCatalogScope();

  return (
    <>
      <Label
        label={label || getAttributeLabel(attribute, currentCatalogLocale)}
      />
      <AssetSelector
        onChange={onChange}
        channel={currentCatalogScope}
        locale={currentCatalogLocale}
        value={value}
        assetFamilyIdentifier={attribute.reference_data_name as string}
        multiple={true}
      />
    </>
  );
};

export { AssetCollectionValue };
