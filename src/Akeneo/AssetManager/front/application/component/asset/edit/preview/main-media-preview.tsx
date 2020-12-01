import React from 'react';
import styled from 'styled-components';
import __ from 'akeneoassetmanager/tools/translator';
import EditionAsset, {getEditionAssetMediaData} from 'akeneoassetmanager/domain/model/asset/edition-asset';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {MediaPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/media-preview';
import {getAttributeAsMainMedia} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {FullscreenPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/fullscreen-preview';
import {getLabelInCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {localeReferenceStringValue} from 'akeneoassetmanager/domain/model/locale-reference';
import {
  Action,
  DownloadAction,
  CopyUrlAction,
  ReloadAction,
} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/media';
import {isDataEmpty} from 'akeneoassetmanager/domain/model/asset/data';
import {Subsection, SubsectionHeader} from 'akeneoassetmanager/application/component/app/subsection';
import {doReloadAllPreviews} from 'akeneoassetmanager/application/action/asset/reloadPreview';
import {connect} from 'react-redux';
import {FullscreenIcon} from 'akeneo-design-system';

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
  padding: 0 10px;
  font-weight: normal;
  text-transform: none;

  > ${Action} {
    margin-left: 15px;
  }
`;

const StyledHeader = styled(SubsectionHeader)`
  position: initial;
`;

type MainMediaPreviewProps = {
  asset: EditionAsset;
  context: {
    channel: ChannelCode;
    locale: LocaleCode;
  };
};

export const MainMediaPreview = connect(null, dispatch => ({
  onReloadPreview: () => dispatch(doReloadAllPreviews() as any),
}))(({asset, context, onReloadPreview}: MainMediaPreviewProps & {onReloadPreview: () => void}) => {
  const attributeAsMainMedia = getAttributeAsMainMedia(asset.assetFamily);
  const data = getEditionAssetMediaData(asset, context.channel, context.locale);
  const attributeLabel = getLabelInCollection(
    attributeAsMainMedia.labels,
    localeReferenceStringValue(context.locale),
    true,
    attributeAsMainMedia.code
  );

  return (
    <Subsection>
      <StyledHeader top={192}>
        <span>{__('pim_asset_manager.asset.enrich.main_media_preview')}</span>
        {!isDataEmpty(data) && (
          <Actions>
            <ReloadAction size={20} data={data} attribute={attributeAsMainMedia} onReload={onReloadPreview} />
            <CopyUrlAction size={20} data={data} attribute={attributeAsMainMedia} />
            <DownloadAction size={20} data={data} attribute={attributeAsMainMedia} />
            <FullscreenPreview anchor={Action} label={attributeLabel} data={data} attribute={attributeAsMainMedia}>
              <FullscreenIcon title={__('pim_asset_manager.asset.button.fullscreen')} size={20} />
            </FullscreenPreview>
          </Actions>
        )}
      </StyledHeader>
      <Container>
        <MediaPreview data={data} label={attributeLabel} attribute={attributeAsMainMedia} />
      </Container>
    </Subsection>
  );
});
