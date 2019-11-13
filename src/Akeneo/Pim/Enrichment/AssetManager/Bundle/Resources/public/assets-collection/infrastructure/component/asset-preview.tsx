import * as React from 'react';
import styled from 'styled-components';
import __ from 'akeneoassetmanager/tools/translator';
import {AssetCode, ProductIdentifier} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/product';
import {
  Asset,
  getAssetByCode,
  getAssetCodes,
  getAssetLabel,
  getNextAssetCode,
  getPreviousAssetCode,
} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {ContextState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
import {CloseButton} from 'akeneoassetmanager/application/component/app/close-button';
import {Modal, SubTitle, Title} from 'akeneopimenrichmentassetmanager/platform/component/common/modal';
import {Attribute} from 'akeneopimenrichmentassetmanager/platform/model/structure/attribute';
import {Carousel} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-preview/carousel';
import {Preview} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-preview/preview';
import Left from 'akeneoassetmanager/application/component/app/icon/left';
import Right from 'akeneoassetmanager/application/component/app/icon/right';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import Key from 'akeneoassetmanager/tools/key';
import {TransparentButton} from 'akeneoassetmanager/application/component/app/button';

const Container = styled.div`
  position: relative;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
`;

const AssetContainer = styled.div`
  overflow-x: auto;
  flex-grow: 1;
  margin: 0 50px;
`;

const PreviewContainer = styled.div`
  display: flex;
  height: calc(100vh - 320px);
  min-height: 450px;
`;

const ArrowButton = styled(TransparentButton)`
  margin: 0 10px;
`;

type AssetPreviewProps = {
  assetCollection: Asset[];
  initialAssetCode: AssetCode;
  productIdentifier: ProductIdentifier | null;
  productAttribute: Attribute;
  context: ContextState;
  onClose: () => void;
};

export const AssetPreview = ({
  assetCollection,
  initialAssetCode,
  productIdentifier,
  productAttribute,
  context,
  onClose,
}: AssetPreviewProps) => {
  const [currentAssetCode, setCurrentAssetCode] = React.useState(initialAssetCode);
  const selectedAsset = getAssetByCode(assetCollection, currentAssetCode);

  if (!selectedAsset) {
    return null;
  }

  const selectedAssetLabel = getAssetLabel(selectedAsset, context.locale);
  const assetCodeCollection = getAssetCodes(assetCollection);
  const setPreviousAsset = () => setCurrentAssetCode(assetCode => getPreviousAssetCode(assetCodeCollection, assetCode));
  const setNextAsset = () => setCurrentAssetCode(assetCode => getNextAssetCode(assetCodeCollection, assetCode));

  React.useEffect(() => {
    const handleArrowNavigation = (event: KeyboardEvent) => {
      if (Key.ArrowLeft === event.key) {
        setPreviousAsset();
      } else if (Key.ArrowRight === event.key) {
        setNextAsset();
      }
    };
    document.addEventListener('keydown', handleArrowNavigation);
    return () => document.removeEventListener('keydown', handleArrowNavigation);
  }, []);
  return (
    <Modal data-role="asset-preview-modal">
      <Container>
        <CloseButton title={__('pim_asset_manager.close')} onClick={onClose} />
        <ArrowButton title={__('pim_asset_manager.asset_preview.previous')} onClick={setPreviousAsset}>
          <Left size={44} color={akeneoTheme.color.grey100} />
        </ArrowButton>
        <AssetContainer>
          <SubTitle>
            {__('pim_asset_manager.breadcrumb.products')} / {productIdentifier}
          </SubTitle>
          <Title>{selectedAssetLabel}</Title>
          <PreviewContainer>
            <Preview context={context} asset={selectedAsset} />
          </PreviewContainer>
          <Carousel
            context={context}
            selectedAssetCode={selectedAsset.code}
            productAttribute={productAttribute}
            assetCollection={assetCollection}
            onAssetChange={setCurrentAssetCode}
          />
        </AssetContainer>
        <ArrowButton title={__('pim_asset_manager.asset_preview.next')} onClick={setNextAsset}>
          <Right size={44} color={akeneoTheme.color.grey100} />
        </ArrowButton>
      </Container>
    </Modal>
  );
};
