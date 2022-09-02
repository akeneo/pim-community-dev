import React, {useEffect, useState} from 'react';
import {SelectInput, useIsMounted} from 'akeneo-design-system';
import {getLabel, useRoute, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {AssetFamilyListItem} from 'akeneoassetmanager/domain/model/asset-family/list';

const useAssetFamilies = (): AssetFamilyListItem[] => {
  const route = useRoute('akeneo_asset_manager_asset_family_index_rest');
  const isMounted = useIsMounted();
  const [assetFamilies, setAssetFamilies] = useState<AssetFamilyListItem[]>([]);

  useEffect(() => {
    const fetchAll = async () => {
      const response = await fetch(route);

      if (response.ok && isMounted()) {
        const {items} = await response.json();

        setAssetFamilies(items);
      }
    };

    fetchAll();
  }, []);

  return assetFamilies;
};

type AssetFamilyFieldProps = {
  assetFamilyIdentifier: string | null;
  readOnly: boolean;
  onChange: (assetFamilyIdentifier: string) => void;
};

const AssetFamilyField = ({assetFamilyIdentifier, readOnly, onChange}: AssetFamilyFieldProps) => {
  const translate = useTranslate();
  const assetFamilies = useAssetFamilies();
  const catalogLocale = useUserContext().get('catalogLocale');

  return (
    <SelectInput
      readOnly={readOnly}
      clearable={false}
      emptyResultLabel={translate('pim_common.no_result')}
      openLabel={translate('pim_common.open')}
      placeholder={translate('pim_enrich.entity.attribute.property.asset_family.default_label')}
      value={assetFamilyIdentifier}
      onChange={onChange}
    >
      {assetFamilies.map(assetFamily => (
        <SelectInput.Option
          key={assetFamily.identifier}
          title={getLabel(assetFamily.labels, catalogLocale, assetFamily.identifier)}
          value={assetFamily.identifier}
        >
          {getLabel(assetFamily.labels, catalogLocale, assetFamily.identifier)}
        </SelectInput.Option>
      ))}
    </SelectInput>
  );
};

export {AssetFamilyField, AssetFamilyFieldProps};
