import React from 'react';
import styled from 'styled-components';
import __ from 'akeneoassetmanager/tools/translator';
import {ContextState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
import {TransparentButton} from 'akeneoassetmanager/application/component/app/button';
import {getAssetEditUrl, getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import ListAsset, {
  getListAssetMainMediaThumbnail,
  MoveDirection,
  getAssetLabel,
  assetWillNotMoveInCollection,
} from 'akeneoassetmanager/domain/model/asset/list-asset';
import {useRegenerate} from 'akeneoassetmanager/application/hooks/regenerate';
import {ArrowLeftIcon, ArrowRightIcon, CloseIcon, EditIcon, AkeneoThemedProps, getColor} from 'akeneo-design-system';

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
  background-color: ${getColor('grey', 140)};
  opacity: 0;
  transition: opacity 0.2s ease-in-out;
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
`;

const Container = styled.div<{readonly: boolean} & AkeneoThemedProps>`
  position: relative;
  width: 140px;
  height: 140px;
  outline: 1px solid ${getColor('grey', 100)};
  opacity: ${(props: AkeneoThemedProps<{readonly: boolean}>) => (props.readonly ? 0.4 : 1)};

  &:hover ${Overlay} {
    opacity: 0.6;
  }
`;

const IconButton = styled(TransparentButton)`
  width: 22px;
  height: 22px;
  color: ${getColor('grey', 100)};

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
  color: ${getColor('white')};

  &:not(:first-child) {
    margin-top: 6px;
  }
`;

const Label = styled.span`
  margin-left: 5px;
  font-size: ${(props: AkeneoThemedProps<{readonly: boolean}>) => props.theme.fontSize.small};
`;

const RemoveAction = (props: any) => (
  <Action {...props}>
    <CloseIcon size={14} />
    <Label>{__('pim_asset_manager.asset_collection.remove_asset')}</Label>
  </Action>
);

const EditAction = (props: any) => (
  <Action {...props} className={'edit-asset-from-thumbnail'} target="_blank">
    <EditIcon size={14} />
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
  assetCollection: ListAsset[];
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

  const previewUrl = getMediaPreviewUrl(getListAssetMainMediaThumbnail(asset, context.channel, context.locale));
  const [, , refreshedUrl] = useRegenerate(previewUrl);

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
              <ArrowLeftIcon />
            </MoveButton>
          ) : (
            <div></div>
          )}
          {!assetWillNotMoveInCollection(assetCollection, asset, MoveDirection.After) ? (
            <MoveButton title={moveAfterLabel} onClick={() => onMove(MoveDirection.After)} data-move-right={asset.code}>
              <ArrowRightIcon />
            </MoveButton>
          ) : (
            <div></div>
          )}
        </Overlay>
      )}
      <Img
        src={refreshedUrl}
        onError={ev => {
          const thumbnailPreviewUrl = '/media/show/undefined/thumbnail_small';
          if (ev.currentTarget.src !== thumbnailPreviewUrl) {
            ev.currentTarget.src = thumbnailPreviewUrl;
          }
        }}
        data-testid={'thumbnail-preview'}
      />
    </Container>
  );
};
