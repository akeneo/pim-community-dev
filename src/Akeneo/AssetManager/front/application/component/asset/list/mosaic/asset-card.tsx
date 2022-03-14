import React, {useCallback, useEffect, useState} from 'react';
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
import {Card, Link} from 'akeneo-design-system';
import {CompletenessBadge} from 'akeneoassetmanager/application/component/app/completeness';
import Completeness from 'akeneoassetmanager/domain/model/asset/completeness';
import {useRouter} from '@akeneo-pim-community/shared';

type AssetCardProps = {
  asset: ListAsset;
  context: Context;
  isSelected: boolean;
  isDisabled: boolean;
  assetWithLink?: boolean;
  onSelectionChange?: (code: AssetCode, value: boolean) => void;
  onClick?: (code: AssetCode) => void;
  shouldGeneratePreview: boolean;
  handlePreviewGenerated: (code: AssetCode) => void;
};

const AssetCard = ({
  asset,
  context,
  isSelected,
  onSelectionChange,
  isDisabled,
  assetWithLink = false,
  onClick,
  shouldGeneratePreview,
  handlePreviewGenerated,
}: AssetCardProps) => {
  const router = useRouter();
  const [url, setUrl] = useState<string | null>(null);
  const imageUrl = getMediaPreviewUrl(router, getListAssetMainMediaThumbnail(asset, context.channel, context.locale));
  const [, , refreshedUrl] = useRegenerate(imageUrl);
  const emptyMediaUrl = getMediaPreviewUrl(router, emptyMediaPreview());
  const assetLabel = getAssetLabel(asset, context.locale);
  const [previewGeneratedUrl, setPreviewGeneratedUrl] = useState<string | null>(null);

  const assetEditUrl = router.generate('akeneo_asset_manager_asset_edit', {
    assetFamilyIdentifier: asset.assetFamilyIdentifier,
    assetCode: asset.code,
    tab: 'enrich',
  });

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

  /**
   * PIM-10306: We want to avoid the preview generation to be done when the component is rendered
   * The preview generation is managed by the parent component
   */
  useEffect(() => {
    const fetchPreviewUrl = async (url: string) => fetch(url);
    if (shouldGeneratePreview && null !== url) {
      fetchPreviewUrl(url).then(() => {
        setPreviewGeneratedUrl(url);
        handlePreviewGenerated(asset.code);
      });
    }
  }, [url, shouldGeneratePreview]);

  const handleClick = useCallback(() => {
    onClick?.(asset.code);
  }, [onClick]);

  const handleSelect = useCallback(
    (newValue: boolean) => {
      onSelectionChange && onSelectionChange(asset.code, newValue);
    },
    [onSelectionChange, asset]
  );

  return (
    <Card
      src={previewGeneratedUrl}
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
      {assetWithLink && undefined !== onClick ? <Link href={`#${assetEditUrl}`}>{assetLabel}</Link> : assetLabel}
    </Card>
  );
};

export default AssetCard;
