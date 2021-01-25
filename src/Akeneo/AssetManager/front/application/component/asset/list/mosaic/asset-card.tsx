import React, {useCallback, useEffect} from 'react';
import {Context} from 'akeneoassetmanager/domain/model/context';
import {getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import ListAsset, {
  assetHasCompleteness,
  getAssetLabel,
  getListAssetMainMediaThumbnail,
} from 'akeneoassetmanager/domain/model/asset/list-asset';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import loadImage from 'akeneoassetmanager/tools/image-loader';
import {useRegenerate} from 'akeneoassetmanager/application/hooks/regenerate';
import {emptyMediaPreview} from 'akeneoassetmanager/domain/model/asset/media-preview';
import {Card} from 'akeneo-design-system';
import {CompletenessBadge} from 'akeneoassetmanager/application/component/app/completeness';
import Completeness from 'akeneoassetmanager/domain/model/asset/completeness';

type AssetCardProps = {
  asset: ListAsset;
  context: Context;
  isSelected: boolean;
  isDisabled: boolean;
  onSelectionChange?: (code: AssetCode, value: boolean) => void;
  onClick?: (code: AssetCode) => void;
};

const AssetCard = ({asset, context, isSelected, onSelectionChange, isDisabled, onClick}: AssetCardProps) => {
  const [url, setUrl] = React.useState<string | null>(null);

  const imageUrl = getMediaPreviewUrl(getListAssetMainMediaThumbnail(asset, context.channel, context.locale));
  const [, , refreshedUrl] = useRegenerate(imageUrl);
  const emptyMediaUrl = getMediaPreviewUrl(emptyMediaPreview());

  let isDisplayed = true;
  useEffect(() => {
    loadImage(refreshedUrl)
      .then(() => {
        if (isDisplayed) {
          setUrl(refreshedUrl);
        }
      })
      .catch(() => {
        setUrl(emptyMediaUrl);
      });

    return () => {
      isDisplayed = false;
    };
  }, [asset, context.channel, context.locale]);

  const handleClick = useCallback(() => {
    onClick && onClick(asset.code);
  }, [onClick]);

  const handleSelect = useCallback(
    (newValue: boolean) => {
      onSelectionChange && onSelectionChange(asset.code, newValue);
    },
    [onSelectionChange, asset]
  );

  return (
    <Card
      src={url}
      fit="contain"
      isSelected={isSelected}
      onClick={undefined === onClick ? undefined : handleClick}
      onSelect={undefined === onSelectionChange && undefined !== onClick ? undefined : handleSelect}
      disabled={isDisabled}
    >
      {assetHasCompleteness(asset) && (
        <Card.BadgeContainer>
          <CompletenessBadge completeness={Completeness.createFromNormalized(asset.completeness)} />
        </Card.BadgeContainer>
      )}
      {getAssetLabel(asset, context.locale)}
    </Card>
  );
};

export default AssetCard;
