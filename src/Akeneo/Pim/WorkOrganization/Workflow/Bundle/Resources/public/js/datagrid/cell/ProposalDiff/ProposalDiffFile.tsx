import React from "react";
import { useRouter } from "@akeneo-pim-community/legacy-bridge";
import { ImageCard } from "./ImageCard";

type File = { fileKey: string, originalFileName: string };

type ProposalDiffFileProps = {
  accessor: 'before' | 'after',
  change: {
    before: File | null;
    after: File | null;
  }
}

const ProposalDiffFile: React.FC<ProposalDiffFileProps> = ({
  accessor,
  change,
  ...rest
}) => {
  const router = useRouter();

  if (change[accessor]) {
    return <ImageCard
      color={accessor === 'before' ? 'green' : 'red'}
      filePath={(change[accessor] as File).fileKey}
      originalFilename={(change[accessor] as File).originalFileName}
      downloadUrl={router.generate('pim_enrich_media_download', {
        'filename': (change[accessor] as File).fileKey,
      })}
      state={accessor === 'before' ? 'removed' : 'added'}
    />
  }

  return <></>;
}

class ProposalDiffFileMatcher {
  static supports(attributeType: string) {
    return [
      'pim_catalog_file', // OK
    ].includes(attributeType);
  }

  static render() {
    return ProposalDiffFile
  }
}

export {ProposalDiffFileMatcher};
