import * as React from 'react';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {AssetFamilyListItem} from 'akeneoassetmanager/domain/model/asset-family/list';
import {getLabel} from 'pimui/js/i18n';
import Select2 from 'akeneoassetmanager/application/component/app/select2';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import __ from 'akeneoassetmanager/tools/translator';
import {ColumnTitle} from 'akeneoassetmanager/application/component/app/column';
import {AssetFamilyDataProvider} from 'akeneoassetmanager/application/hooks/asset-family';

type AssetFamilySelectorProps = {
  assetFamilyIdentifier: AssetFamilyIdentifier | null;
  locale: LocaleCode;
  dataProvider: AssetFamilyDataProvider;
  onChange: (assetFamilyIdentifier: AssetFamilyIdentifier | null) => void;
};

export const useAssetFamilyList = (
  currentAssetFamilyIdentifier: AssetFamilyIdentifier | null,
  dataProvider: AssetFamilyDataProvider,
  onChange: (assetFamily: AssetFamilyIdentifier | null) => void
): [AssetFamilyListItem[], boolean] => {
  const [assetFamilyList, setAssetFamilyList] = React.useState<AssetFamilyListItem[]>([]);
  const [isFetching, setIsFetching] = React.useState(true);

  React.useEffect(() => {
    if (isFetching) return;

    if (0 === assetFamilyList.length) {
      //if the family list is empty, we set the asset family identifier to null
      onChange(null);
    } else if (
      //If we cannot find the asset family, we set the first asset family
      !assetFamilyList.some(
        assetFamily => null !== currentAssetFamilyIdentifier && assetFamily.identifier === currentAssetFamilyIdentifier
      )
    ) {
      onChange(assetFamilyList[0].identifier);
    }
  }, [assetFamilyList, isFetching]);

  React.useEffect(() => {
    dataProvider.assetFamilyFetcher.fetchAll().then((assetFamilyList: AssetFamilyListItem[]) => {
      setAssetFamilyList(assetFamilyList);
      setIsFetching(false);
    });
  }, []);

  return [assetFamilyList, isFetching];
};

/* istanbul ignore next */
export const AssetFamilySelector = ({
  assetFamilyIdentifier,
  locale,
  dataProvider,
  onChange,
}: AssetFamilySelectorProps) => {
  const [assetFamilyList, isFetching] = useAssetFamilyList(assetFamilyIdentifier, dataProvider, onChange);

  const data = assetFamilyList.reduce(
    (result, assetFamily) => ({
      ...result,
      [assetFamily.identifier]: getLabel(assetFamily.labels, locale, assetFamily.identifier),
    }),
    {}
  );

  return (
    <>
      <ColumnTitle>{__('pim_asset_manager.asset_family.column.selector.title')}</ColumnTitle>
      {null !== assetFamilyIdentifier && !isFetching ? (
        <Select2
          light
          data={data}
          value={assetFamilyIdentifier}
          multiple={false}
          readOnly={false}
          configuration={{}}
          onChange={onChange}
        />
      ) : (
        __('pim_asset_manager.asset_family.column.selector.no_data')
      )}
    </>
  );
};
