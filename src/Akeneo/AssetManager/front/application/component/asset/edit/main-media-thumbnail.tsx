import React from 'react';
import styled from 'styled-components';
import {getColor} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import EditionAsset, {
  getEditionAssetLabel,
  getEditionAssetMainMediaThumbnail,
} from 'akeneoassetmanager/domain/model/asset/edition-asset';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {useRegenerate} from 'akeneoassetmanager/application/hooks/regenerate';
import {emptyMediaPreview} from 'akeneoassetmanager/domain/model/asset/media-preview';
import {useReloadPreview} from 'akeneoassetmanager/application/hooks/useReloadPreview';

type MainMediaThumbnailProps = {
  asset: EditionAsset;
  context: {
    channel: ChannelCode;
    locale: LocaleCode;
  };
};

const Container = styled.div`
  position: relative;
  width: 142px;
  height: 142px;
  border: 1px solid ${getColor('grey', 80)};
  margin-right: 20px;
  border-radius: 4px;
  display: flex;
  overflow: hidden;
  flex-basis: 142px;
  flex-shrink: 0;
`;

const Img = styled.img`
  margin: auto;
  width: 100%;
  max-height: 140px;
  transition: filter 0.3s;
  z-index: 0;
  object-fit: contain;
`;

const MainMediaThumbnail = ({asset, context}: MainMediaThumbnailProps) => {
  const translate = useTranslate();
  const url = getMediaPreviewUrl(getEditionAssetMainMediaThumbnail(asset, context.channel, context.locale));
  const emptyMediaUrl = getMediaPreviewUrl(emptyMediaPreview());
  const label = getEditionAssetLabel(asset, context.locale);
  const [regenerate, doRegenerate, refreshedUrl] = useRegenerate(url);
  const [reloadPreview] = useReloadPreview();

  React.useEffect(() => {
    if (reloadPreview) {
      doRegenerate();
    }
  }, [reloadPreview]);

  return regenerate ? (
    <div className="AknLoadingPlaceHolderContainer" title={label}>
      <Container />
    </div>
  ) : (
    <Container>
      <Img
        alt={translate('pim_asset_manager.asset.img', {label})}
        src={refreshedUrl}
        onError={event => (event.target as HTMLInputElement).setAttribute('src', emptyMediaUrl)}
      />
    </Container>
  );
};

export {MainMediaThumbnail};
