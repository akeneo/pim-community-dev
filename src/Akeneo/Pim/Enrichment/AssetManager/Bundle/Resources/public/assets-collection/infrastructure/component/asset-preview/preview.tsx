import * as React from 'react';
import styled from 'styled-components';
import {Asset, getAssetLabel} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import __ from 'akeneoassetmanager/tools/translator';
import Download from 'akeneoassetmanager/application/component/app/icon/download';
import Fullscreen from 'akeneoassetmanager/application/component/app/icon/fullscreen';
import {MediaPreviewTypes, getAssetPreview} from 'akeneoassetmanager/tools/media-url-generator';

const Container = styled.div`
  display: flex;
  justify-content: center;
  align-items: center;
  flex: 1;
`;

const Border = styled.div`
  display: flex;
  flex-direction: column;
  padding: 20px;
  border: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
  height: fit-content;
  width: fit-content;
`;

const Image = styled.img`
  object-fit: contain;
  height: 100%;
  max-width: 100%;
  max-height: calc(100% - 44px);
`;

const Actions = styled.div`
  display: flex;
  align-items: center;
  justify-content: center;
  padding-top: 20px;
`;

const Action = styled.a`
  display: flex;
  align-items: center;

  &:not(:first-child) {
    margin-left: 20px;
  }

  &:hover {
    cursor: pointer;
  }
`;

const Label = styled.span`
  margin-left: 5px;
  color: ${(props: ThemedProps<void>) => props.theme.color.grey100};
  text-transform: capitalize;
`;

type PreviewProps = {
  asset: Asset;
  context: Context;
};

export const Preview = ({asset, context}: PreviewProps) => {
  console.log(asset);
  return (
    <Container>
      <Border>
        <Image
          src={getAssetPreview(asset, MediaPreviewTypes.Preview)}
          alt={getAssetLabel(asset, context.locale)}
          data-role="asset-preview"
        />
        <Actions>
          <Action href={'asset.image'} download>
            <Download />
            <Label>{__('pim_asset_manager.download')}</Label>
          </Action>
          <Action href={'asset.image'} target="_blank">
            <Fullscreen />
            <Label>{__('pim_asset_manager.fullscreen')}</Label>
          </Action>
        </Actions>
      </Border>
    </Container>
  );
};
