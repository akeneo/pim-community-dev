import React from 'react';
import assetFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset';
import {getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import EditionAsset, {getEditionAssetMainMediaThumbnail} from 'akeneoassetmanager/domain/model/asset/edition-asset';
import {ImageCard} from './ImageCard';
import {ProposalChangeAccessor} from '../ProposalChange';
const UserContext = require('pim/user-context');

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

  React.useEffect(() => {
    (change[accessor] || []).forEach((assetCode, i) => {
      assetFetcher.fetch(change.attributeReferenceDataName, assetCode).then(result => {
        assets[i] = result.asset;
        setAssets([...assets]);
      });
    });
  }, []);

  return (
    <div {...rest}>
      {assets.map((asset, i) => {
        let thumbnailUrl;
        if (asset) {
          thumbnailUrl = getMediaPreviewUrl(
            getEditionAssetMainMediaThumbnail(asset, UserContext.get('catalogScope'), UserContext.get('catalogLocale'))
          );
        }

        const isDiff =
          accessor === 'before'
            ? !(change['after'] || []).includes((change['before'] || [])[i])
            : !(change['before'] || []).includes((change['after'] || [])[i]);

        return (
          <ImageCard
            thumbnailUrl={thumbnailUrl}
            filePath={(change[accessor] || [])[i]}
            originalFilename={asset?.code || ''}
            downloadUrl={thumbnailUrl}
            key={`${asset?.code}-${i}`}
            state={isDiff ? (accessor === 'before' ? 'removed' : 'added') : undefined}
          />
        );
      })}
    </div>
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

export {ProposalDiffAssetCollectionMatcher};
