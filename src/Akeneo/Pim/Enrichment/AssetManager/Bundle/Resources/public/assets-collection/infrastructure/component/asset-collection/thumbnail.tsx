import * as React from 'react';
import styled from 'styled-components';
import {akeneoTheme, opacity, ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import Close from 'akeneoassetmanager/application/component/app/icon/close';
import __ from 'akeneoassetmanager/tools/translator';
import {ContextState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
import Right from 'akeneoassetmanager/application/component/app/icon/right';
import Left from 'akeneoassetmanager/application/component/app/icon/left';
import {TransparentButton} from 'akeneoassetmanager/application/component/app/button';
import {getAssetEditUrl, getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import Edit from 'akeneoassetmanager/application/component/app/icon/edit';
import ListAsset, {getListAssetMainMediaThumbnail, MoveDirection, getAssetLabel, assetWillNotMoveInCollection} from 'akeneoassetmanager/domain/model/asset/list-asset';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';

const Img = styled.img`
  width: 140px;
  height: 140px;
  object-fit: contain;
`;

const Overlay = styled.div`
  position: absolute;
  width: 100%;
  height: 100%;
  padding: 10px;
  background-color: ${(props: ThemedProps<void>) => opacity(props.theme.color.grey140, 0.6)};
  opacity: 0;
  transition: opacity 0.2s ease-in-out;
  display: flex;
  align-items: center;
  justify-content: space-between;
`;

const Container = styled.div<{readonly: boolean}>`
  position: relative;
  width: 140px;
  height: 140px;
  outline: 1px solid ${(props: ThemedProps<{readonly: boolean}>) => props.theme.color.grey100};
  opacity: ${(props: ThemedProps<{readonly: boolean}>) => (props.readonly ? 0.4 : 1)};

  &:hover ${Overlay} {
    opacity: 1;
  }
`;

const IconButton = styled(TransparentButton)`
  width: 22px;
  height: 22px;

  &:hover g {
    stroke: white;
  }
`;

const MoveButton: React.FunctionComponent<{title: string} & any> = ({title, children, ...props}) => (
  <IconButton title={title} tabIndex={0} {...props}>
    {children}
  </IconButton>
);

const Actions = styled.div`
  position: absolute;
  bottom: 10px;
  display: flex;
  flex-direction: column;
  align-items: baseline;
`;

const Action = styled.a`
  display: flex;
  align-items: center;
  cursor: pointer;
  line-height: 14px;

  &:not(:first-child) {
    margin-top: 6px;
  }
`;

const Label = styled.span`
  margin-left: 5px;
  color: white;
  font-size: ${(props: ThemedProps<{readonly: boolean}>) => props.theme.fontSize.small};
`;

const RemoveAction = (props: any) => (
  <Action {...props}>
    <Close size={14} color="white" />
    <Label>{__('pim_asset_manager.asset_collection.remove_asset')}</Label>
  </Action>
);

const EditAction = (props: any) => (
  <Action {...props} target="_blank">
    <Edit size={14} color="white" />
    <Label>{__('pim_asset_manager.asset_collection.edit_asset')}</Label>
  </Action>
);

export const Thumbnail = ({
  asset,
  context,
  readonly,
  assetCollection,
  onRemove,
  onMove,
  onClick,
}: {
  asset: ListAsset;
  context: ContextState;
  readonly: boolean;
  assetCollection: AssetCode[];
  onRemove: () => void;
  onMove: (direction: MoveDirection) => void;
  onClick?: () => void;
}) => {
  const moveAfterLabel = __('pim_asset_manager.asset_collection.move_asset_to_right', {
    assetName: getAssetLabel(asset, context.locale),
  });
  const moveBeforeLabel = __('pim_asset_manager.asset_collection.move_asset_to_left', {
    assetName: getAssetLabel(asset, context.locale),
  });

  const overlayRef = React.useRef(null);
  const handleOverlayClick = (event: React.MouseEvent) => {
    if (event.target === overlayRef.current) {
      undefined !== onClick && onClick();
    }
  };

  return (
    <Container readonly={readonly}>
      {!readonly && (
        <Overlay onClick={handleOverlayClick} ref={overlayRef} data-testid="overlay">
          <Actions>
            <RemoveAction onClick={onRemove} data-remove={asset.code} />
            <EditAction href={getAssetEditUrl(asset)} data-edit={asset.code} />
          </Actions>
          {!assetWillNotMoveInCollection(assetCollection, asset, MoveDirection.Before) ? (
            <MoveButton
              title={moveBeforeLabel}
              onClick={() => onMove(MoveDirection.Before)}
              data-move-left={asset.code}
            >
              <Left color={akeneoTheme.color.grey100} />
            </MoveButton>
          ) : (
            <div />
          )}
          {!assetWillNotMoveInCollection(assetCollection, asset, MoveDirection.After) ? (
            <MoveButton title={moveAfterLabel} onClick={() => onMove(MoveDirection.After)} data-move-right={asset.code}>
              <Right color={akeneoTheme.color.grey100} />
            </MoveButton>
          ) : (
            <div />
          )}
        </Overlay>
      )}
      <Img src={getMediaPreviewUrl(getListAssetMainMediaThumbnail(asset, context.channel, context.locale))} />
    </Container>
  );
};
