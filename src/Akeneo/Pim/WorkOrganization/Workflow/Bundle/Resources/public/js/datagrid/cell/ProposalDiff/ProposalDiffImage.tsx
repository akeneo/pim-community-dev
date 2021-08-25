import React from 'react';
import {useRouter} from '@akeneo-pim-community/shared';
import {ImageCard} from './ImageCard';
import {ProposalChangeAccessor} from '../ProposalChange';

type Image = {fileKey: string; originalFileName: string};

type ProposalDiffImageProps = {
  accessor: ProposalChangeAccessor;
  change: {
    before: Image | null;
    after: Image | null;
  };
};

const ProposalDiffImage: React.FC<ProposalDiffImageProps> = ({accessor, change, ...rest}) => {
  const router = useRouter();

  if (change[accessor]) {
    const data = change[accessor] as Image;

    const thumbnailUrl = router.generate('pim_enrich_media_show', {
      filename: data.fileKey,
      filter: 'thumbnail',
    });
    const downloadUrl = router.generate('pim_enrich_media_download', {
      filename: data.fileKey,
    });

    return (
      <ImageCard
        thumbnailUrl={thumbnailUrl}
        filePath={data.fileKey}
        originalFilename={data.originalFileName}
        downloadUrl={downloadUrl}
        state={accessor === 'before' ? 'removed' : 'added'}
        {...rest}
      />
    );
  }

  return <span {...rest} />;
};

export default ProposalDiffImage;
