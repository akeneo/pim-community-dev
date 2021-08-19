import React from 'react';
import {ProposalChangeData} from './Proposal';
import {ProposalDiffFallbackMatcher} from "./ProposalDiff";

export type ProposalChangeAccessor = 'before' | 'after';

type ProposalChangeProps = {
  attributeType: string;
  change: ProposalChangeData;
  accessor: ProposalChangeAccessor;
  className: string;
  matchers: ProposalChangeMatcherConfig;
};

export type ProposalChangeMatcherConfig = {[key: string]: {
  matcher: {
    supports: (attributeType: string) => boolean;
    render: () => React.FC<{
      accessor: ProposalChangeAccessor;
      change: {before: any; after: any};
      className: string;
    }>
  }
}};

const ProposalChange: React.FC<ProposalChangeProps> = ({change, accessor, className, matchers, ...rest}) => {
  const matcher = Object.values(matchers).find(matcher => matcher.matcher.supports(change.attributeType)) || { matcher: ProposalDiffFallbackMatcher };
  const ProposalDiff = matcher.matcher.render();

  return <ProposalDiff accessor={accessor} change={change} className={className} {...rest} />;
};

export {ProposalChange};
