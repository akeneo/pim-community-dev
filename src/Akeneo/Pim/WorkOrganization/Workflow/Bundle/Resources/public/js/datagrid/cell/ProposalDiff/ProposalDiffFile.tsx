import React from 'react';
import {useRouter} from '@akeneo-pim-community/shared';
import {ImageCard} from './ImageCard';
import {ProposalChangeAccessor} from '../ProposalChange';

type File = {fileKey: string; originalFileName: string};

type ProposalDiffFileProps = {
  accessor: ProposalChangeAccessor;
  change: {
    before: File | null;
    after: File | null;
  };
};

const ProposalDiffFile: React.FC<ProposalDiffFileProps> = ({accessor, change, ...rest}) => {
  const router = useRouter();

  if (change[accessor]) {
    const data = change[accessor] as File;
    const encodedFileName = encodeURIComponent(data.fileKey);

    return (
      <ImageCard
        filePath={data.fileKey}
        originalFilename={data.originalFileName}
        downloadUrl={router.generate('pim_enrich_media_download', {
          filename: encodedFileName,
        })}
        state={accessor === 'before' ? 'removed' : 'added'}
        {...rest}
      />
    );
  }

  return <span {...rest} />;
};

export default ProposalDiffFile;
