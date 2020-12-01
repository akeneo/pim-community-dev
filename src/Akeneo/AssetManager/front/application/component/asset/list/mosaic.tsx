import React from 'react';
import styled from 'styled-components';
import {Context} from 'akeneoassetmanager/domain/model/context';
import AssetCard, {AssetCardWithLink} from 'akeneoassetmanager/application/component/asset/list/mosaic/asset-card';
import __ from 'akeneoassetmanager/tools/translator';
import EmptyResult from 'akeneoassetmanager/application/component/asset/list/mosaic/empty-result';
import ListAsset, {
  isAssetInCollection,
  addAssetToCollection,
  removeAssetFromAssetCodeCollection,
  ASSET_COLLECTION_LIMIT,
} from 'akeneoassetmanager/domain/model/asset/list-asset';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {Helper} from 'akeneo-design-system';

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
  scrollContainerRef = React.useRef<null | HTMLDivElement>(null),
  context,
  selection,
  onSelectionChange,
  assetCollection,
  hasReachMaximumSelection,
  resultCount,
  onAssetClick,
  assetHasLink = false,
}: {
  scrollContainerRef?: React.RefObject<HTMLDivElement>;
  selection: AssetCode[];
  assetCollection: ListAsset[];
  context: Context;
  resultCount: number | null;
  hasReachMaximumSelection: boolean;
  onSelectionChange: (selection: AssetCode[]) => void;
  onAssetClick?: (asset: AssetCode) => void;
  assetHasLink?: boolean;
}) => {
  const AssetCardComponent = assetHasLink ? AssetCardWithLink : AssetCard;

  return (
    <React.Fragment>
      {hasReachMaximumSelection && (
        <Helper>{__('pim_asset_manager.asset_collection.notification.limit', {limit: ASSET_COLLECTION_LIMIT})}</Helper>
      )}
      {assetCollection.length > 0 ? (
        <Container data-container="mosaic" ref={scrollContainerRef}>
          <Grid>
            {assetCollection.map((asset: ListAsset) => {
              const isSelected = isAssetInCollection(asset.code, selection);

              return (
                <AssetCardComponent
                  key={asset.code}
                  asset={asset}
                  context={context}
                  isSelected={isSelected}
                  isDisabled={hasReachMaximumSelection && !isSelected}
                  onSelectionChange={(code: AssetCode, isChecked: boolean) => {
                    const newSelection = isChecked
                      ? addAssetToCollection(selection, code)
                      : removeAssetFromAssetCodeCollection(selection, code);
                    onSelectionChange(newSelection);
                  }}
                  onClick={onAssetClick}
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
