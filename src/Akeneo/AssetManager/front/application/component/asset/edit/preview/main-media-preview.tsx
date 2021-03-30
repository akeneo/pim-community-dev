import React from 'react';
import styled from 'styled-components';
import {SectionTitle, useBooleanState} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Section} from '@akeneo-pim-community/shared';
import EditionAsset, {getEditionAssetMediaData} from 'akeneoassetmanager/domain/model/asset/edition-asset';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {MediaPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/media-preview';
import {getAttributeAsMainMedia} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {FullscreenPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/fullscreen-preview';
import {getLabelInCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {localeReferenceStringValue} from 'akeneoassetmanager/domain/model/locale-reference';
import {
  DownloadAction,
  CopyUrlAction,
  ReloadAction,
  FullscreenAction,
} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/media';
import {isDataEmpty} from 'akeneoassetmanager/domain/model/asset/data';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  margin: 20px 0;
  max-height: calc(100vh - 500px);
  min-height: 250px;
  position: relative;
`;

const Actions = styled.div`
  display: flex;
  gap: 2px;
`;

type MainMediaPreviewProps = {
  asset: EditionAsset;
  context: {
    channel: ChannelCode;
    locale: LocaleCode;
  };
};

const MainMediaPreview = ({asset, context}: MainMediaPreviewProps) => {
  const translate = useTranslate();
  const [isFullscreenModalOpen, openFullscreenModal, closeFullscreenModal] = useBooleanState();
  const attributeAsMainMedia = getAttributeAsMainMedia(asset.assetFamily);
  const data = getEditionAssetMediaData(asset, context.channel, context.locale);
  const attributeLabel = getLabelInCollection(
    attributeAsMainMedia.labels,
    localeReferenceStringValue(context.locale),
    true,
    attributeAsMainMedia.code
  );

  return (
    <Section>
      <SectionTitle sticky={192}>
        <SectionTitle.Title>{translate('pim_asset_manager.asset.enrich.main_media_preview')}</SectionTitle.Title>
        <SectionTitle.Spacer />
        {!isDataEmpty(data) && (
          <Actions>
            <ReloadAction data={data} attribute={attributeAsMainMedia} />
            <CopyUrlAction data={data} attribute={attributeAsMainMedia} />
            <DownloadAction data={data} attribute={attributeAsMainMedia} />
            <FullscreenAction onClick={openFullscreenModal} />
            {isFullscreenModalOpen && (
              <FullscreenPreview
                onClose={closeFullscreenModal}
                label={attributeLabel}
                data={data}
                attribute={attributeAsMainMedia}
              />
            )}
          </Actions>
        )}
      </SectionTitle>
      <Container>
        <MediaPreview data={data} label={attributeLabel} attribute={attributeAsMainMedia} />
      </Container>
    </Section>
  );
};

export {MainMediaPreview};
