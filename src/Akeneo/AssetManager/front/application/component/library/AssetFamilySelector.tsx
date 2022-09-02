import React, {useEffect, useRef, useState} from 'react';
import {
  AssetsIllustration,
  Dropdown,
  Search,
  SwitcherButton,
  useAutoFocus,
  useBooleanState,
} from 'akeneo-design-system';
import {useTranslate, getLabel, LocaleCode} from '@akeneo-pim-community/shared';
import {AssetFamilyListItem} from 'akeneoassetmanager/domain/model/asset-family/list';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {useAssetFamilyFetcher} from 'akeneoassetmanager/infrastructure/fetcher/useAssetFamilyFetcher';

type AssetFamilySelectorProps = {
  assetFamilyIdentifier: AssetFamilyIdentifier | null;
  locale: LocaleCode;
  onChange: (assetFamilyIdentifier: AssetFamilyIdentifier | null) => void;
};

const useAssetFamilyList = (
  currentAssetFamilyIdentifier: AssetFamilyIdentifier | null,
  onChange: (assetFamily: AssetFamilyIdentifier | null) => void
): [AssetFamilyListItem[], boolean] => {
  const [assetFamilyList, setAssetFamilyList] = useState<AssetFamilyListItem[]>([]);
  const [isFetching, setIsFetching] = useState(true);
  const assetFamilyFetcher = useAssetFamilyFetcher();

  useEffect(() => {
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

  useEffect(() => {
    assetFamilyFetcher.fetchAll().then((assetFamilyList: AssetFamilyListItem[]) => {
      setAssetFamilyList(assetFamilyList);
      setIsFetching(false);
    });
  }, []);

  return [assetFamilyList, isFetching];
};

/* istanbul ignore next */
const AssetFamilySelector = ({assetFamilyIdentifier, locale, onChange}: AssetFamilySelectorProps) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState();
  const [searchValue, setSearchValue] = useState<string>('');
  const inputRef = useRef<HTMLInputElement>(null);
  const focus = useAutoFocus(inputRef);
  const [assetFamilyList, isFetching] = useAssetFamilyList(assetFamilyIdentifier, onChange);

  const handleClose = () => {
    close();
    setSearchValue('');
  };

  const handleItemClick = (assetFamilyIdentifier: AssetFamilyIdentifier) => () => {
    handleClose();
    onChange(assetFamilyIdentifier);
  };

  useEffect(() => {
    isOpen && focus();
  }, [isOpen, focus]);

  const selectedAssetFamily = assetFamilyList.find(({identifier}) => identifier === assetFamilyIdentifier) ?? null;

  const filteredAssetFamilyList = assetFamilyList.filter(
    ({identifier, labels}) =>
      identifier.toLowerCase().includes(searchValue.toLowerCase()) ||
      getLabel(labels, locale, identifier)
        .toLowerCase()
        .includes(searchValue.toLowerCase())
  );

  return null !== assetFamilyIdentifier && !isFetching ? (
    <Dropdown>
      <SwitcherButton
        inline={false}
        label={translate('pim_asset_manager.asset_family.column.selector.title')}
        onClick={open}
      >
        {getLabel(selectedAssetFamily?.labels ?? {}, locale, assetFamilyIdentifier)}
      </SwitcherButton>
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={handleClose}>
          <Dropdown.Header>
            <Search
              inputRef={inputRef}
              searchValue={searchValue}
              onSearchChange={setSearchValue}
              title={translate('pim_common.search')}
              placeholder={translate('pim_common.search')}
            />
          </Dropdown.Header>
          <Dropdown.ItemCollection
            noResultIllustration={<AssetsIllustration />}
            noResultTitle={translate('pim_common.no_result')}
          >
            {filteredAssetFamilyList.map(({identifier, labels}) => (
              <Dropdown.Item key={identifier} onClick={handleItemClick(identifier)}>
                {getLabel(labels, locale, identifier)}
              </Dropdown.Item>
            ))}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  ) : (
    <>{translate('pim_asset_manager.asset_family.column.selector.no_data')}</>
  );
};

export {AssetFamilySelector, useAssetFamilyList};
