import React from "react";
import assetFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset';
import { getMediaPreviewUrl } from "akeneoassetmanager/tools/media-url-generator";
import EditionAsset, { getEditionAssetMainMediaThumbnail } from "akeneoassetmanager/domain/model/asset/edition-asset";
import { ImageCard } from "./ImageCard";
const UserContext = require('pim/user-context');

type AssetFamilyIdentifier = string;

type ProposalDiffAssetCollectionProps = {
  accessor: 'before_data' | 'after_data',
  change: {
    attributeReferenceDataName: AssetFamilyIdentifier;
    before_data: string[];
    after_data: string[];
  }
}

const ProposalDiffAssetCollection: React.FC<ProposalDiffAssetCollectionProps> = ({
  accessor,
  change,
  ...rest
}) => {
  const [assets, setAssets] = React.useState<(EditionAsset | undefined)[]>(new Array((change[accessor] || []).length));

  React.useEffect(() => {
    (change[accessor] || []).forEach((assetCode, i) => {
      assetFetcher.fetch(change.attributeReferenceDataName, assetCode).then((result) => {
        assets[i] = result.asset;
        setAssets([...assets]);
      })
    });
  }, []);

  return <>
    {assets.map((asset, i) => {
      let thumbnailUrl;
      if (asset) {
        thumbnailUrl = getMediaPreviewUrl(getEditionAssetMainMediaThumbnail(
          asset,
          UserContext.get('catalogScope'),
          UserContext.get('catalogLocale')
        ))
      }

      const isDiff = accessor === 'before_data' ?
        !(change['after_data'] || []).includes((change['before_data'] || [])[i]) :
        !(change['before_data'] || []).includes((change['after_data'] || [])[i]);

      return <ImageCard
        thumbnailUrl={thumbnailUrl}
        filePath={(change[accessor] || [])[i]}
        originalFilename={asset?.code || ''}
        downloadUrl={thumbnailUrl} // TODO Fix this
        key={`${asset?.code}-${i}`}
        state={isDiff ? (accessor === 'before_data' ? 'removed' : 'added') : undefined}
      />
    })}
  </>
}

class ProposalDiffAssetCollectionMatcher {
  static supports(attributeType: string) {
    return [
      'pim_catalog_asset_collection', // OK
    ].includes(attributeType);
  }

  static render() {
    return ProposalDiffAssetCollection
  }
}

export {ProposalDiffAssetCollectionMatcher};
