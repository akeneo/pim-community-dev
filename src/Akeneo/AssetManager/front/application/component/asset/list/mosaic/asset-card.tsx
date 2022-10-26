import React, {RefObject, useCallback, useRef, useState} from 'react';
import {Context} from 'akeneoassetmanager/domain/model/context';
import {getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import ListAsset, {
  assetHasCompleteness,
  getAssetLabel,
  getListAssetMainMediaThumbnail,
} from 'akeneoassetmanager/domain/model/asset/list-asset';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import addToLoadingQueue from 'akeneoassetmanager/tools/image-loader';
import {useRegenerate} from 'akeneoassetmanager/application/hooks/regenerate';
import {emptyMediaPreview} from 'akeneoassetmanager/domain/model/asset/media-preview';
import {Card, Link} from 'akeneo-design-system';
import {CompletenessBadge} from 'akeneoassetmanager/application/component/app/completeness';
import Completeness from 'akeneoassetmanager/domain/model/asset/completeness';
import {useRouter, useIsMounted} from '@akeneo-pim-community/shared';
import {useOnFirstScreenDisplay} from '../../../../hooks/useOnFirstScreenDisplay';

type AssetCardProps = {
  asset: ListAsset;
  context: Context;
  isSelected: boolean;
  isDisabled: boolean;
  assetWithLink?: boolean;
  onSelectionChange?: (code: AssetCode, value: boolean) => void;
  onClick?: (code: AssetCode) => void;
  containerRef: RefObject<Element>;
};

const AssetCard = ({
  asset,
  context,
  containerRef,
  isSelected,
  onSelectionChange,
  isDisabled,
  assetWithLink = false,
  onClick,
}: AssetCardProps) => {
  const router = useRouter();
  const ref = useRef(null);
  const [url, setUrl] = useState<string | null>(null);
  const imageUrl = getMediaPreviewUrl(router, getListAssetMainMediaThumbnail(asset, context.channel, context.locale));
  const [, , refreshedUrl] = useRegenerate(imageUrl);
  const emptyMediaUrl = getMediaPreviewUrl(router, emptyMediaPreview());
  const assetLabel = getAssetLabel(asset, context.locale);
  const isMounted = useIsMounted();

  const assetEditUrl = router.generate('akeneo_asset_manager_asset_edit', {
    assetFamilyIdentifier: asset.assetFamilyIdentifier,
    assetCode: asset.code,
    tab: 'enrich',
  });

  useOnFirstScreenDisplay(
    () => {
      addToLoadingQueue(refreshedUrl)
        .then(() => {
          if (isMounted()) {
            setUrl(refreshedUrl);
          }
        })
        .catch(() => {
          setUrl(emptyMediaUrl);
        });
    },
    ref,
    containerRef,
    '50%'
  );

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
      ref={ref}
      loading="lazy"
      src={url}
      fit="contain"
      isSelected={isSelected}
      onClick={undefined === onClick ? undefined : handleClick}
      onSelect={undefined === onSelectionChange && undefined !== onClick ? undefined : handleSelect}
      disabled={isDisabled}
    >
      {assetHasCompleteness(asset) && (
        <Card.BadgeContainer stacked={false}>
          <CompletenessBadge completeness={Completeness.createFromNormalized(asset.completeness)} />
        </Card.BadgeContainer>
      )}
      {assetWithLink && undefined !== onClick ? <Link href={`#${assetEditUrl}`}>{assetLabel}</Link> : assetLabel}
    </Card>
  );
};

export default AssetCard;
