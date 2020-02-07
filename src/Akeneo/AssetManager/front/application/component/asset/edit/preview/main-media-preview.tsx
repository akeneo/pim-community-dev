import * as React from 'react';
import styled from 'styled-components';
import __ from 'akeneoassetmanager/tools/translator';
import EditionAsset, {getEditionAssetMediaData} from 'akeneoassetmanager/domain/model/asset/edition-asset';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {MediaPreview} from './media-preview';
import {getAttributeAsMainMedia} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {FullscreenPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/fullscreen-preview';
import {Fullscreen} from 'akeneoassetmanager/application/component/app/icon/fullscreen';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {getLabelInCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {localeReferenceStringValue} from 'akeneoassetmanager/domain/model/locale-reference';
import {
  Action,
  DownloadAction,
  CopyUrlAction,
} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/media-actions';
import {isDataEmpty} from 'akeneoassetmanager/domain/model/asset/data';
import {Subsection, SubsectionHeader} from 'akeneoassetmanager/application/component/app/subsection';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  margin: 20px 0;
  max-height: calc(100vh - 500px);
  min-height: 250px;
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

type MainMediaPreviewProps = {
  asset: EditionAsset;
  context: {
    channel: ChannelCode;
    locale: LocaleCode;
  };
};

export const MainMediaPreview = ({asset, context}: MainMediaPreviewProps) => {
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
      <SubsectionHeader>
        <span>{__('pim_asset_manager.asset.enrich.main_media_preview')}</span>
        {!isDataEmpty(data) && (
          <Actions>
            <DownloadAction color={akeneoTheme.color.grey100} size={20} data={data} attribute={attributeAsMainMedia} />
            <CopyUrlAction color={akeneoTheme.color.grey100} size={20} data={data} attribute={attributeAsMainMedia} />
            <FullscreenPreview anchor={Action} label={attributeLabel} data={data} attribute={attributeAsMainMedia}>
              <Fullscreen
                title={__('pim_asset_manager.asset.button.fullscreen')}
                color={akeneoTheme.color.grey100}
                size={20}
              />
            </FullscreenPreview>
          </Actions>
        )}
      </SubsectionHeader>
      <Container>
        <MediaPreview data={data} label={attributeLabel} attribute={attributeAsMainMedia} />
      </Container>
    </Subsection>
  );
};
