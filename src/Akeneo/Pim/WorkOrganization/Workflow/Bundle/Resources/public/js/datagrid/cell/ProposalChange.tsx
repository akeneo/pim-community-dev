import React from 'react';
import {ProposalChangeData} from './Proposal';
import {ProposalDiffFallback} from './ProposalDiff';

export type ProposalChangeAccessor = 'before' | 'after';

type ProposalChangeProps = {
  attributeType: string;
  change: ProposalChangeData;
  accessor: ProposalChangeAccessor;
  className: string;
  matchers: ProposalChangeMatcherConfig;
};

export type ProposalChangeMatcherConfig = {
  [attributeType: string]: {
    default: React.FC<{
      accessor: ProposalChangeAccessor;
      change: {before: any; after: any};
      className: string;
    }>;
  };
};

const ProposalChange: React.FC<ProposalChangeProps> = ({change, accessor, className, matchers, ...rest}) => {
  const ProposalDiff = matchers[change.attributeType]?.default || ProposalDiffFallback;

  return <ProposalDiff accessor={accessor} change={change} className={className} {...rest} />;
};

export {ProposalChange};
