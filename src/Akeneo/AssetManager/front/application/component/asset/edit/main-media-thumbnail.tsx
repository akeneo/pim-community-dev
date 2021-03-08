import * as React from 'react';
import EditionAsset, {
  getEditionAssetLabel,
  getEditionAssetMainMediaThumbnail,
} from 'akeneoassetmanager/domain/model/asset/edition-asset';
import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {doReloadAllPreviews} from 'akeneoassetmanager/application/action/asset/reloadPreview';
import {connect} from 'react-redux';
import {EditState} from 'akeneoassetmanager/application/reducer/asset/edit';
import {useRegenerate} from 'akeneoassetmanager/application/hooks/regenerate';
import {emptyMediaPreview} from 'akeneoassetmanager/domain/model/asset/media-preview';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

type MainMediaThumbnailProps = {
  asset: EditionAsset;
  context: {
    channel: ChannelCode;
    locale: LocaleCode;
  };
  reloadPreview: boolean;
};

const Container = styled.div`
  position: relative;
  width: 142px;
  height: 142px;
  border: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
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

export const DisconnectedMainMediaThumbnail = ({asset, context, reloadPreview}: MainMediaThumbnailProps) => {
  const translate = useTranslate();
  const url = getMediaPreviewUrl(getEditionAssetMainMediaThumbnail(asset, context.channel, context.locale));
  const emptyMediaUrl = getMediaPreviewUrl(emptyMediaPreview());
  const label = getEditionAssetLabel(asset, context.locale);
  const [regenerate, doRegenerate, refreshedUrl] = useRegenerate(url);

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

export const MainMediaThumbnail = connect(
  (state: EditState) => ({
    reloadPreview: state.reloadPreview,
  }),
  dispatch => ({
    onReloadPreview: () => dispatch(doReloadAllPreviews() as any),
  })
)(DisconnectedMainMediaThumbnail);
