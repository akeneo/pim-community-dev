import React from 'react';
import styled from 'styled-components';
import __ from 'akeneoassetmanager/tools/translator';
import {ProductIdentifier} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/product';
import {ContextState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
import {CloseButton} from 'akeneoassetmanager/application/component/app/close-button';
import {Modal, SubTitle, Title} from 'akeneoassetmanager/application/component/app/modal';
import {Attribute} from 'akeneoassetmanager/platform/model/structure/attribute';
import {Carousel} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/carousel';
import {TransparentButton} from 'akeneoassetmanager/application/component/app/button';
import {getAttributeAsMainMedia} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import ListAsset, {
  getAssetByCode,
  getAssetLabel,
  getAssetCodes,
  getPreviousAssetCode,
  getNextAssetCode,
  getListAssetMediaData,
} from 'akeneoassetmanager/domain/model/asset/list-asset';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {useAssetFamily, AssetFamilyDataProvider} from 'akeneoassetmanager/application/hooks/asset-family';
import {getAssetEditUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {MediaPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/media-preview';
import {Border, PreviewContainer} from 'akeneoassetmanager/application/component/asset/edit/preview/fullscreen-preview';
import {
  Action,
  ActionLabel,
  Actions,
  DownloadAction,
  CopyUrlAction,
} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/media';
import {ArrowLeftIcon, ArrowRightIcon, EditIcon, getColor, Key, useShortcut} from 'akeneo-design-system';

const Container = styled.div`
  position: relative;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  height: 100%;
`;

const AssetContainer = styled.div`
  display: flex;
  flex: 1;
  flex-direction: column;
  justify-content: space-between;
  margin: 0 50px;
  height: 100%;
  overflow-x: auto;
`;

const Header = styled.div``;

const ArrowButton = styled(TransparentButton)`
  margin: 0 10px;
  color: ${getColor('grey', 100)};
`;

const StyledPreviewContainer = styled(PreviewContainer)`
  max-height: calc(100vh - 350px);
`;

type AssetPreviewProps = {
  assetCollection: ListAsset[];
  initialAssetCode: AssetCode;
  productIdentifier: ProductIdentifier | null;
  productAttribute: Attribute;
  context: ContextState;
  assetFamilyIdentifier: AssetFamilyIdentifier;
  onClose: () => void;
  dataProvider: AssetFamilyDataProvider;
};

const EditAction = ({url, label}: {url: string; label: string}) => (
  <Action className={'edit-asset-from-preview'} title={label} href={url} target="_blank">
    <EditIcon />
    <ActionLabel title={label}>{label}</ActionLabel>
  </Action>
);

//TODO Use DSM Modal
export const AssetPreview = ({
  assetCollection,
  initialAssetCode,
  productIdentifier,
  productAttribute,
  assetFamilyIdentifier,
  context,
  onClose,
  dataProvider,
}: AssetPreviewProps) => {
  const [currentAssetCode, setCurrentAssetCode] = React.useState(initialAssetCode);
  const selectedAsset: ListAsset | undefined = getAssetByCode(assetCollection, currentAssetCode);
  const {assetFamily} = useAssetFamily(dataProvider, assetFamilyIdentifier);
  const assetCodeCollection = getAssetCodes(assetCollection);
  const setPreviousAsset = () => setCurrentAssetCode(assetCode => getPreviousAssetCode(assetCodeCollection, assetCode));
  const setNextAsset = () => setCurrentAssetCode(assetCode => getNextAssetCode(assetCodeCollection, assetCode));

  useShortcut(Key.ArrowLeft, setPreviousAsset);
  useShortcut(Key.ArrowRight, setNextAsset);

  if (undefined === selectedAsset || null === assetFamily) {
    return null;
  }

  const selectedAssetLabel = getAssetLabel(selectedAsset, context.locale);
  const editUrl = getAssetEditUrl(selectedAsset);
  const data = getListAssetMediaData(selectedAsset, context.channel, context.locale);
  const attributeAsMainMedia = getAttributeAsMainMedia(assetFamily);

  return (
    <Modal role="dialog">
      <Container>
        <CloseButton title={__('pim_asset_manager.close')} onClick={onClose} />
        <ArrowButton title={__('pim_asset_manager.asset_preview.previous')} onClick={setPreviousAsset}>
          <ArrowLeftIcon size={44} />
        </ArrowButton>
        <AssetContainer>
          <Header>
            <SubTitle>
              {__('pim_asset_manager.breadcrumb.products')} / {productIdentifier}
            </SubTitle>
            <Title>{selectedAssetLabel}</Title>
          </Header>
          <StyledPreviewContainer>
            <Border>
              <MediaPreview data={data} label={selectedAssetLabel} attribute={attributeAsMainMedia} />
              <Actions margin={20}>
                <CopyUrlAction
                  data={data}
                  attribute={attributeAsMainMedia}
                  label={__('pim_asset_manager.asset_preview.copy_url')}
                />
                <DownloadAction
                  data={data}
                  attribute={attributeAsMainMedia}
                  label={__('pim_asset_manager.asset_preview.download')}
                />
                <EditAction url={editUrl} label={__('pim_asset_manager.asset_preview.edit_asset')} />
              </Actions>
            </Border>
          </StyledPreviewContainer>
          <Carousel
            context={context}
            selectedAssetCode={selectedAsset.code}
            productAttribute={productAttribute}
            assetCollection={assetCollection}
            onAssetChange={setCurrentAssetCode}
          />
        </AssetContainer>
        <ArrowButton title={__('pim_asset_manager.asset_preview.next')} onClick={setNextAsset}>
          <ArrowRightIcon size={44} />
        </ArrowButton>
      </Container>
    </Modal>
  );
};
