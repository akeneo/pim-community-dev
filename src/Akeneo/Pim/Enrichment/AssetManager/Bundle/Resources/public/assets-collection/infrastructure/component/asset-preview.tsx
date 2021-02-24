import React, {useState} from 'react';
import styled from 'styled-components';
import {ArrowLeftIcon, ArrowRightIcon, EditIcon, getColor, Key, Modal, useShortcut} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {ProductIdentifier} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/product';
import {ContextState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
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
import {
  Action,
  ActionLabel,
  Actions,
  DownloadAction,
  CopyUrlAction,
} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/media';

const Container = styled.div`
  position: relative;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  height: 100%;
  width: 100%;
  padding: 20px 0;
`;

const Border = styled.div`
  display: flex;
  flex-direction: column;
  padding: 20px;
  border: 1px solid ${getColor('grey', 80)};
  max-height: 100%;
  gap: 20px;
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

const Header = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
`;

const ArrowButton = styled(TransparentButton)`
  margin: 0 10px;
  color: ${getColor('grey', 100)};
`;

const PreviewContainer = styled.div`
  display: flex;
  justify-content: center;
  align-items: center;
  max-height: calc(100vh - 350px);
`;

const BrandedTitle = styled(Modal.Title)`
  color: ${getColor('brand', 100)};
`;

const EditAction = ({url, label}: {url: string; label: string}) => (
  <Action className="edit-asset-from-preview" title={label} href={url} target="_blank">
    <EditIcon />
    <ActionLabel title={label}>{label}</ActionLabel>
  </Action>
);

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

const AssetPreview = ({
  assetCollection,
  initialAssetCode,
  productIdentifier,
  productAttribute,
  assetFamilyIdentifier,
  context,
  onClose,
  dataProvider,
}: AssetPreviewProps) => {
  const translate = useTranslate();
  const [currentAssetCode, setCurrentAssetCode] = useState<AssetCode>(initialAssetCode);
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
    <Modal closeTitle={translate('pim_common.close')} onClose={onClose}>
      <Container>
        <ArrowButton title={translate('pim_asset_manager.asset_preview.previous')} onClick={setPreviousAsset}>
          <ArrowLeftIcon size={44} />
        </ArrowButton>
        <AssetContainer>
          <Header>
            <Modal.SectionTitle>
              {translate('pim_asset_manager.breadcrumb.products')} / {productIdentifier}
            </Modal.SectionTitle>
            <BrandedTitle>{selectedAssetLabel}</BrandedTitle>
          </Header>
          <PreviewContainer>
            <Border>
              <MediaPreview data={data} label={selectedAssetLabel} attribute={attributeAsMainMedia} />
              <Actions margin={20}>
                <CopyUrlAction
                  data={data}
                  attribute={attributeAsMainMedia}
                  label={translate('pim_asset_manager.asset_preview.copy_url')}
                />
                <DownloadAction
                  data={data}
                  attribute={attributeAsMainMedia}
                  label={translate('pim_asset_manager.asset_preview.download')}
                />
                <EditAction url={editUrl} label={translate('pim_asset_manager.asset_preview.edit_asset')} />
              </Actions>
            </Border>
          </PreviewContainer>
          <Carousel
            context={context}
            selectedAssetCode={selectedAsset.code}
            productAttribute={productAttribute}
            assetCollection={assetCollection}
            onAssetChange={setCurrentAssetCode}
          />
        </AssetContainer>
        <ArrowButton title={translate('pim_asset_manager.asset_preview.next')} onClick={setNextAsset}>
          <ArrowRightIcon size={44} />
        </ArrowButton>
      </Container>
    </Modal>
  );
};

export {AssetPreview};
