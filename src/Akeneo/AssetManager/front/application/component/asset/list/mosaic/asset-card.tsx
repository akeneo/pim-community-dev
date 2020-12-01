import * as React from 'react';
import {Context} from 'akeneoassetmanager/domain/model/context';
import styled from 'styled-components';
import {Label} from 'akeneoassetmanager/application/component/app/label';
import Checkbox from 'akeneoassetmanager/application/component/app/checkbox';
import {akeneoTheme, ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import CompletenessBadge from 'akeneoassetmanager/application/component/asset/list/mosaic/completeness-badge';
import {getAssetEditUrl, getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import ListAsset, {
  assetHasCompleteness,
  getAssetLabel,
  getListAssetMainMediaThumbnail,
} from 'akeneoassetmanager/domain/model/asset/list-asset';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import loadImage from 'akeneoassetmanager/tools/image-loader';
import {useRegenerate} from 'akeneoassetmanager/application/hooks/regenerate';

type ContainerProps = {isDisabled: boolean};
const Container = styled.div<ContainerProps>`
  position: relative;
  display: flex;
  flex-direction: column;
  width: 100%;
  justify-content: space-between;
  cursor: ${(props: ContainerProps) => (props.isDisabled ? 'not-allowed' : 'auto')};
`;
const Title = styled.span`
  display: flex;
  align-items: center;
  min-height: 15px;
`;
const Thumbnail = styled.img`
  object-fit: contain;
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
`;

const AssetCompleteness = styled.div`
  position: absolute;
  top: 10px;
  right: 10px;
`;

type ImageContainerProps = {isSelected: boolean; isSelectable: boolean; isLoading: boolean};
const ImageContainer = styled.div<ImageContainerProps>`
  width: 100%;
  padding-top: 100%; /* 1:1 Aspect Ratio */
  position: relative;
  border-width: ${(props: ThemedProps<ImageContainerProps>) => (props.isSelected ? '2px' : '1px')};
  width: ${(props: ThemedProps<ImageContainerProps>) => (props.isSelected ? 'calc(100% - 2px)' : '100%')};
  border-color: ${(props: ThemedProps<ImageContainerProps>) =>
    props.isSelected ? props.theme.color.blue100 : props.theme.color.grey100};
  border-style: ${(props: ThemedProps<ImageContainerProps>) => (props.isLoading ? 'none' : 'solid')};
  margin-bottom: 6px;
  min-height: 140px;

  &:hover {
    cursor: ${(props: ThemedProps<ImageContainerProps>) => (props.isSelectable ? 'pointer' : 'auto')};
  }
`;

type AssetCardProps = {
  asset: ListAsset;
  context: Context;
  isSelected: boolean;
  isDisabled: boolean;
  onSelectionChange: (code: AssetCode, value: boolean) => void;
  onClick?: (code: AssetCode) => void;
};

export const AssetCardWithLink = ({...props}: AssetCardProps) => {
  const assetUrl = getAssetEditUrl(props.asset);

  return (
    <a href={assetUrl} onClick={e => e.preventDefault()}>
      <AssetCard {...props} />
    </a>
  );
};

const AssetCard = ({asset, context, isSelected, onSelectionChange, isDisabled, onClick}: AssetCardProps) => {
  const [url, setUrl] = React.useState<string | null>(null);

  const imageUrl = getMediaPreviewUrl(getListAssetMainMediaThumbnail(asset, context.channel, context.locale));
  const [, , refreshedUrl] = useRegenerate(imageUrl);

  let isDisplayed = true;
  React.useEffect(() => {
    loadImage(refreshedUrl)
      .then(() => {
        if (isDisplayed) {
          setUrl(refreshedUrl);
        }
      })
      .catch(() => {
        setUrl(null);
      });

    return () => {
      isDisplayed = false;
    };
  }, [asset, context.channel, context.locale]);

  return (
    <Container data-asset={asset.code} data-selected={isSelected} isDisabled={isDisabled}>
      <ImageContainer
        className={null === url ? 'AknLoadingPlaceHolder' : ''}
        isLoading={null === url}
        isSelectable={!!onClick}
        isSelected={isSelected}
        onClick={() => {
          if (onClick) {
            onClick(asset.code);
          } else if (!isDisabled) {
            onSelectionChange(asset.code, !isSelected);
          }
        }}
      >
        {null !== url && <Thumbnail data-testid="asset-card-image" src={url} />}
      </ImageContainer>
      {assetHasCompleteness(asset) && (
        <AssetCompleteness>
          <CompletenessBadge completeness={asset.completeness} />
        </AssetCompleteness>
      )}
      <Title>
        {!onClick && (
          <Checkbox
            value={isSelected}
            onChange={(value: boolean) => onSelectionChange(asset.code, value)}
            readOnly={isDisabled}
          />
        )}
        <Label color={isSelected ? akeneoTheme.color.blue100 : undefined}>{getAssetLabel(asset, context.locale)}</Label>
      </Title>
    </Container>
  );
};

export default AssetCard;
