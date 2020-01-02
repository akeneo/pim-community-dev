import * as React from 'react';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {AssetFamilyListItem} from 'akeneoassetmanager/domain/model/asset-family/list';
import {getLabel} from 'pimui/js/i18n';
import Select2 from 'akeneoassetmanager/application/component/app/select2';
import {AssetFamilyFetcher} from 'akeneoassetmanager/infrastructure/fetcher/asset-family';

type AssetFamilySelectorProps = {
  assetFamilyIdentifier: AssetFamilyIdentifier | null;
  locale: LocaleCode;
  dataProvider: {
    assetFamilyFetcher: AssetFamilyFetcher;
  };
  onChange: (assetFamilyIdentifier: AssetFamilyIdentifier | null) => void;
};
export const AssetFamilySelector = ({
  assetFamilyIdentifier,
  locale,
  dataProvider,
  onChange,
}: AssetFamilySelectorProps) => {
  const [assetFamilyList, setAssetFamilyList] = React.useState<AssetFamilyListItem[]>([]);
  const [isFetching, setIsFetching] = React.useState(true);

  React.useEffect(() => {
    if (isFetching) return;

    if (0 === assetFamilyList.length) {
      //if the family list is empty, we set the asset family identifier to null
      onChange(null);
    } else if (
      //If we cannot find the asset family, we set the first asset family
      !assetFamilyList.some(assetFamily => assetFamily.identifier === assetFamilyIdentifier)
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

  const data = assetFamilyList.reduce(
    (result, assetFamily) => ({
      ...result,
      [assetFamily.identifier]: getLabel(assetFamily.labels, locale, assetFamily.identifier),
    }),
    {}
  );

  return (
    <>
      {null !== assetFamilyIdentifier && !isFetching && (
        <Select2
          data={data}
          value={assetFamilyIdentifier}
          multiple={false}
          readOnly={false}
          configuration={{}}
          onChange={(assetFamilyIdentifier: AssetFamilyIdentifier) => {
            onChange(assetFamilyIdentifier);
          }}
        />
      )}
    </>
  );
};
