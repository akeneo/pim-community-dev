import React from 'react';
import {ProposalChangeAccessor} from '../ProposalChange';

type ProposalDiffFallbackProps = {
  accessor: ProposalChangeAccessor;
  change: {
    before: any;
    after: any;
  };
  className: string;
};

const ProposalDiffFallback: React.FC<ProposalDiffFallbackProps> = ({accessor, change, ...rest}) => {
  return <span {...rest}>{JSON.stringify(change[accessor])}</span>;
};

export {ProposalDiffFallback};
