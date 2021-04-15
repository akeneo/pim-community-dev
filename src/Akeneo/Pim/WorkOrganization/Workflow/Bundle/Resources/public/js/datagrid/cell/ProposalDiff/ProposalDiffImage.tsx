import React from "react";
import { useRouter } from "@akeneo-pim-community/legacy-bridge";
import { ImageCard } from "./ImageCard";

type Image = { fileKey: string, originalFileName: string };

type ProposalDiffImageProps = {
  accessor: 'before' | 'after',
  change: {
    before: Image | null;
    after: Image | null;
  }
}

const ProposalDiffImage: React.FC<ProposalDiffImageProps> = ({
  accessor,
  change,
  ...rest
}) => {
  const router = useRouter();

  if (change[accessor]) {
    const data = change[accessor] as Image;

    const thumbnailUrl = router.generate('pim_enrich_media_show', {
      'filename': data.fileKey,
      'filter': 'thumbnail',
    });
    const downloadUrl = router.generate('pim_enrich_media_download', {
      'filename': data.fileKey,
    });

    return <ImageCard
      thumbnailUrl={thumbnailUrl}
      filePath={data.fileKey}
      originalFilename={data.originalFileName}
      downloadUrl={downloadUrl}
      state={accessor === 'before' ? 'removed' : 'added'}
      {...rest}
    />
  }
  return <></>;
}

class ProposalDiffImageMatcher {
  static supports(attributeType: string) {
    return [
      'pim_catalog_image', // OK
    ].includes(attributeType);
  }

  static render() {
    return ProposalDiffImage
  }
}

export {ProposalDiffImageMatcher};
