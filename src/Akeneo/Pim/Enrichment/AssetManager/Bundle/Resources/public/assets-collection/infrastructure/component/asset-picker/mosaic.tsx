import * as React from 'react';
import {
  Asset,
  isAssetInCollection,
  addAssetToCollection,
  removeAssetFromCollection,
} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import {AssetCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/product';
import AssetCard from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/mosaic/asset-card';
import styled from 'styled-components';
import __ from 'akeneoassetmanager/tools/translator';
import EmptyResult from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/mosaic/empty-result';
import {AssetCollectionLimitNotification} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-collection/asset-collection-limit-notification';

const Container = styled.div`
  height: 100%;
  overflow-y: auto;
  flex: 1;
`;

const Grid = styled.div`
  margin-top: 20px;
  display: grid;
  grid-gap: 20px;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
`;

const MoreResults = styled.div`
  margin-top: 20px;
`;

const MAX_DISPLAYED_ASSETS = 500;

const Mosaic = ({
  context,
  selection,
  onSelectionChange,
  assetCollection,
  hasReachMaximumSelection,
  resultCount,
}: {
  selection: AssetCode[];
  assetCollection: Asset[];
  context: Context;
  resultCount: number | null;
  hasReachMaximumSelection: boolean;
  onSelectionChange: (selection: AssetCode[]) => void;
}) => {
  return (
    <React.Fragment>
      {hasReachMaximumSelection && <AssetCollectionLimitNotification />}
      {assetCollection.length > 0 ? (
        <Container data-container="mosaic">
          <Grid>
            {assetCollection.map((asset: Asset) => {
              const isSelected = isAssetInCollection(asset.code, selection);

              return (
                <AssetCard
                  key={asset.code}
                  asset={asset}
                  context={context}
                  isSelected={isSelected}
                  isDisabled={hasReachMaximumSelection && !isSelected}
                  onSelectionChange={(code: AssetCode, isChecked: boolean) => {
                    const newSelection = isChecked
                      ? addAssetToCollection(selection, code)
                      : removeAssetFromCollection(selection, code);
                    onSelectionChange(newSelection);
                  }}
                />
              );
            })}
          </Grid>
          <MoreResults>
            {null !== resultCount &&
            resultCount >= MAX_DISPLAYED_ASSETS &&
            assetCollection.length === MAX_DISPLAYED_ASSETS ? (
              <div className="AknDescriptionHeader AknDescriptionHeader--sticky">
                <div
                  className="AknDescriptionHeader-icon"
                  style={{backgroundImage: 'url("/bundles/pimui/images/illustrations/Product.svg")'}}
                />
                <div className="AknDescriptionHeader-title">
                  {__('pim_asset_manager.asset.grid.more_result.title')}
                  <div className="AknDescriptionHeader-description">
                    {__('pim_asset_manager.asset.grid.more_result.description', {
                      total: resultCount,
                      maxDisplayedAssets: MAX_DISPLAYED_ASSETS,
                    })}
                  </div>
                </div>
              </div>
            ) : null}
          </MoreResults>
        </Container>
      ) : (
        <EmptyResult />
      )}
    </React.Fragment>
  );
};

export default Mosaic;
