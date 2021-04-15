import React from 'react';
import {useRouter} from '@akeneo-pim-community/legacy-bridge';
import {ImageCard} from './ImageCard';
import { ProposalChangeAccessor } from "../ProposalChange";

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
    return (
      <ImageCard
        filePath={(change[accessor] as File).fileKey}
        originalFilename={(change[accessor] as File).originalFileName}
        downloadUrl={router.generate('pim_enrich_media_download', {
          filename: (change[accessor] as File).fileKey,
        })}
        state={accessor === 'before' ? 'removed' : 'added'}
        {...rest}
      />
    );
  }

  return <span {...rest}/>;
};

class ProposalDiffFileMatcher {
  static supports(attributeType: string) {
    return [
      'pim_catalog_file',
    ].includes(attributeType);
  }

  static render() {
    return ProposalDiffFile;
  }
}

export {ProposalDiffFileMatcher};
