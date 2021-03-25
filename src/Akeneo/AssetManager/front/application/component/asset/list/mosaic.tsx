import React from 'react';
import styled from 'styled-components';
import {CardGrid, Helper} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Context} from 'akeneoassetmanager/domain/model/context';
import AssetCard from 'akeneoassetmanager/application/component/asset/list/mosaic/asset-card';
import EmptyResult from 'akeneoassetmanager/application/component/asset/list/mosaic/empty-result';
import ListAsset, {ASSET_COLLECTION_LIMIT} from 'akeneoassetmanager/domain/model/asset/list-asset';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';

const MAX_DISPLAYED_ASSETS = 500;

const Container = styled.div`
  height: 100%;
  overflow-y: auto;
  flex: 1;
  padding-top: 20px;
`;
const MoreResults = styled.div`
  margin-top: 20px;
`;

type MosaicProps = {
  scrollContainerRef?: React.RefObject<HTMLDivElement>;
  assetCollection: ListAsset[];
  context: Context;
  resultCount: number | null;
  hasReachMaximumSelection: boolean;
  selectionState: 'mixed' | boolean;
  assetWithLink?: boolean;
  onSelectionChange?: (assetCode: AssetCode, newValue: boolean) => void;
  isItemSelected: (assetCode: AssetCode) => boolean;
  onAssetClick?: (asset: AssetCode) => void;
};

const Mosaic = ({
  scrollContainerRef = React.useRef<null | HTMLDivElement>(null),
  context,
  onSelectionChange,
  isItemSelected,
  assetCollection,
  hasReachMaximumSelection,
  resultCount,
  onAssetClick,
  selectionState,
  assetWithLink = false,
}: MosaicProps) => {
  const translate = useTranslate();

  return (
    <React.Fragment>
      {hasReachMaximumSelection && (
        <Helper>
          {translate('pim_asset_manager.asset_collection.notification.limit', {limit: ASSET_COLLECTION_LIMIT})}
        </Helper>
      )}
      {assetCollection.length > 0 ? (
        <Container data-container="mosaic" ref={scrollContainerRef}>
          <CardGrid>
            {assetCollection.map((asset: ListAsset) => {
              const isSelected = isItemSelected(asset.code);

              return (
                <AssetCard
                  key={asset.code}
                  asset={asset}
                  context={context}
                  isSelected={isSelected}
                  isDisabled={hasReachMaximumSelection && !isSelected}
                  onSelectionChange={onSelectionChange}
                  onClick={!selectionState ? onAssetClick : undefined}
                  assetWithLink={assetWithLink}
                />
              );
            })}
          </CardGrid>
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
                  {translate('pim_asset_manager.asset.grid.more_result.title')}
                  <div className="AknDescriptionHeader-description">
                    {translate('pim_asset_manager.asset.grid.more_result.description', {
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
