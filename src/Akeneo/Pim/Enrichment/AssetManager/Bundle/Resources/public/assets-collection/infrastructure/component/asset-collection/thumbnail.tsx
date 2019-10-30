import * as React from 'react';
import styled from 'styled-components';
import {akeneoTheme, opacity, ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {
  Asset,
  assetWillNotMoveInCollection,
  getAssetLabel,
  getImage,
  MoveDirection,
} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import Close from 'akeneoassetmanager/application/component/app/icon/close';
import __ from 'akeneoassetmanager/tools/translator';
import {ContextState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
import Right from 'akeneoassetmanager/application/component/app/icon/right';
import Left from 'akeneoassetmanager/application/component/app/icon/left';
import {AssetCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/product';
import {TransparentButton} from 'akeneoassetmanager/application/component/app/button';

const Img = styled.img`
  width: 140px;
  height: 140px;
  object-fit: cover;

  &:hover {
    object-fit: contain;
  }
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

const Container = styled.div`
  position: relative;
  width: 140px;
  height: 140px;
  outline: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey100};

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

const TopLeftSvgButton = styled(IconButton)`
  position: absolute;
  left: 0;
  top: 0;
  margin: 10px;
`;

const RemoveButton = ({title, ...props}: {title: string} & any) => (
  <TopLeftSvgButton title={title} tabIndex={0} {...props}>
    <Close color={akeneoTheme.color.grey100} title={title} />
  </TopLeftSvgButton>
);

const MoveButton: React.FunctionComponent<{title: string} & any> = ({title, children, ...props}) => (
  <IconButton title={title} tabIndex={0} {...props}>
    {children}
  </IconButton>
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
  asset: Asset;
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
  const removeLabel = __('pim_asset_manager.asset_collection.remove_one_asset', {
    assetName: getAssetLabel(asset, context.locale),
  });

  const overlayRef = React.useRef(null);
  const handleOverlayClick = (event: React.MouseEvent) => {
    if (event.target === overlayRef.current) {
      undefined !== onClick && onClick();
    }
  };

  return (
    <Container>
      {!readonly ? (
        <Overlay onClick={handleOverlayClick} ref={overlayRef} data-testid="overlay">
          <RemoveButton title={removeLabel} onClick={onRemove} data-remove={asset.code} />
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
      ) : null}
      <Img src={getImage(asset)} />
    </Container>
  );
};
