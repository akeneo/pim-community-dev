import * as React from 'react'
import styled from 'styled-components';
import {Asset, getImage, getAssetLabel} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {ThemedProps, opacity, akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import Close from 'akeneoassetmanager/application/component/app/icon/close';
import __ from 'akeneoreferenceentity/tools/translator';
import {ContextState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';


export const Thumbnail = ({asset, context, readonly, onRemove}: {asset: Asset, context: ContextState, readonly: boolean, onRemove: () => void}) => {
  const Img = styled.img`
    width: 140px;
    height: 140px;
  `;
  const Overlay = styled.div`
    position: absolute;
    width: 100%;
    height: 100%;
    background-color: ${(props: ThemedProps<void>) => opacity(props.theme.color.grey140, 0.6)}
    opacity: 0;
    transition: opacity .2s ease-in-out;
  `;

  const Container = styled.div`
    position: relative;
    width: 140px;
    height: 140px;
    border: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey100}

    &:hover ${Overlay}, &:focus ${Overlay} {
      opacity: 1;
    }
  `;

  const RemoveButton = styled(Close)`
    position: absolute
    left: 0;
    top: 0;
    margin: 10px;
    width: 22px;
    height: 22px;

    &:hover {
      cursor: pointer;

      g {
        stroke: white;
      }
    }
  `;

  return (
    <Container>
      {!readonly ? (
        <Overlay>
          <RemoveButton color={akeneoTheme.color.grey100} title={__('pim_asset_manager.asset_collection.remove_one_asset', {
            assetName: getAssetLabel(asset, context.locale)
          })} onClick={onRemove}/>
        </Overlay>
      ) : null}
      <Img src={getImage(asset)}/>
    </Container>)
}
