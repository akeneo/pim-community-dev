import React from 'react';
import assetFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset';
import {getMediaPreviewUrl, getImageDownloadUrl} from 'akeneoassetmanager/tools/media-url-generator';
import EditionAsset, {
  getEditionAssetMainMediaThumbnail,
  getEditionAssetMediaData,
  getEditionAssetLabel,
} from 'akeneoassetmanager/domain/model/asset/edition-asset';
import {getAttributeAsMainMedia} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {ImageCard} from './ImageCard';
import {ProposalChangeAccessor} from '../ProposalChange';
import {MediaPreviewType} from 'akeneoassetmanager/domain/model/asset/media-preview';
const UserContext = require('pim/user-context');
import {FullscreenPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/fullscreen-preview';
import {ReloadPreviewProvider} from 'akeneoassetmanager/application/hooks/useReloadPreview';
import {isMediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {MediaTypes} from 'akeneoassetmanager/domain/model/attribute/type/media-link/media-type';
import {isDataEmpty} from 'akeneoassetmanager/domain/model/asset/data';
import {isMediaFileData} from 'akeneoassetmanager/domain/model/asset/data/media-file';
import {getMediaLinkUrl} from 'akeneoassetmanager/domain/model/asset/data/media-link';
import {useRouter} from '@akeneo-pim-community/shared';

type AssetFamilyIdentifier = string;

type ProposalDiffAssetCollectionProps = {
  accessor: ProposalChangeAccessor;
  change: {
    attributeReferenceDataName: AssetFamilyIdentifier;
    before: string[];
    after: string[];
  };
};

const ProposalDiffAssetCollection: React.FC<ProposalDiffAssetCollectionProps> = ({accessor, change, ...rest}) => {
  const [assets, setAssets] = React.useState<(EditionAsset | undefined)[]>(new Array((change[accessor] || []).length));
  const router = useRouter();

  React.useEffect(() => {
    (change[accessor] || []).forEach((assetCode, i) => {
      assetFetcher.fetch(change.attributeReferenceDataName, assetCode).then(result => {
        assets[i] = result.asset;
        setAssets([...assets]);
      });
    });
  }, []);

  return (
    <ReloadPreviewProvider {...rest}>
      {assets.map((asset, i) => {
        if (!asset) {
          return <span key={`undefined-asset-${i}`} />;
        }

        const attribute = getAttributeAsMainMedia(asset.assetFamily);
        const data = getEditionAssetMediaData(asset, UserContext.get('catalogScope'), UserContext.get('catalogLocale'));
        const label = getEditionAssetLabel(asset, UserContext.get('catalogLocale'));
        const thumbnailUrl = getMediaPreviewUrl(
          router,
          getEditionAssetMainMediaThumbnail(
            asset,
            UserContext.get('catalogScope'),
            UserContext.get('catalogLocale'),
            MediaPreviewType.Thumbnail
          )
        );
        let downloadUrl = undefined;
        if (
          !(
            (isMediaLinkAttribute(attribute) &&
              (MediaTypes.youtube === attribute.media_type || MediaTypes.vimeo === attribute.media_type)) ||
            isDataEmpty(data)
          )
        ) {
          downloadUrl = isMediaFileData(data) ? getImageDownloadUrl(router, data) : getMediaLinkUrl(data, attribute);
        }

        const isDiff =
          accessor === 'before'
            ? !(change['after'] || []).includes((change['before'] || [])[i])
            : !(change['before'] || []).includes((change['after'] || [])[i]);

        return (
          <ImageCard
            thumbnailUrl={thumbnailUrl}
            filePath={(change[accessor] || [])[i]}
            originalFilename={label}
            downloadUrl={downloadUrl}
            state={isDiff ? (accessor === 'before' ? 'removed' : 'added') : undefined}
            key={`${asset.code}-${i}`}
          >
            <FullscreenPreview attribute={attribute} data={data} label={label} onClose={() => {}} />
          </ImageCard>
        );
      })}
    </ReloadPreviewProvider>
  );
};

class ProposalDiffAssetCollectionMatcher {
  static supports(attributeType: string) {
    return ['pim_catalog_asset_collection'].includes(attributeType);
  }

  static render() {
    return ProposalDiffAssetCollection;
  }
}

export const matcher = ProposalDiffAssetCollectionMatcher;
