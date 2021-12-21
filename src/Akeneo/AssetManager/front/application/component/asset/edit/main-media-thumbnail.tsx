import React from 'react';
import styled from 'styled-components';
import {Image} from 'akeneo-design-system';
import {ChannelCode, LocaleCode, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import EditionAsset, {
  getEditionAssetLabel,
  getEditionAssetMainMediaThumbnail,
} from 'akeneoassetmanager/domain/model/asset/edition-asset';
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
  margin-right: 20px;
`;

const MainMediaThumbnail = ({asset, context}: MainMediaThumbnailProps) => {
  const translate = useTranslate();
  const router = useRouter();
  const url = getMediaPreviewUrl(router, getEditionAssetMainMediaThumbnail(asset, context.channel, context.locale));
  const emptyMediaUrl = getMediaPreviewUrl(router, emptyMediaPreview());
  const label = getEditionAssetLabel(asset, context.locale);
  const [regenerate, doRegenerate, refreshedUrl] = useRegenerate(url);
  const [reloadPreview] = useReloadPreview();

  React.useEffect(() => {
    if (reloadPreview) {
      doRegenerate();
    }
  }, [reloadPreview]);

  return (
    <Container>
      <Image
        fit="contain"
        width={142}
        height={142}
        alt={translate('pim_asset_manager.asset.img', {label})}
        src={regenerate ? null : refreshedUrl}
        onError={event => (event.target as HTMLInputElement).setAttribute('src', emptyMediaUrl)}
      />
    </Container>
  );
};

export {MainMediaThumbnail};
