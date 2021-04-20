import React from 'react';
import {ProposalChangeData} from './Proposal';
import {
  ProposalDiffStringMatcher,
  ProposalDiffStringArrayMatcher,
  ProposalDiffAssetCollectionMatcher,
  ProposalDiffImageMatcher,
  ProposalDiffFallbackMatcher,
  ProposalDiffFileMatcher,
  ProposalDiffReferenceEntityMatcher,
  ProposalDiffReferenceEntityCollectionMatcher,
  ProposalDiffMeasurementMatcher,
} from './ProposalDiff';

export type ProposalChangeAccessor = 'before' | 'after';

type ProposalChangeProps = {
  attributeType: string;
  change: ProposalChangeData;
  accessor: ProposalChangeAccessor;
  className: string;
};

const ProposalChange: React.FC<ProposalChangeProps> = ({change, accessor, className, ...rest}) => {
  const matcher: {
    render: () => React.FC<{
      accessor: ProposalChangeAccessor;
      change: {before: any; after: any};
      className: string;
    }>;
  } =
    [
      ProposalDiffStringMatcher,
      ProposalDiffStringArrayMatcher,
      ProposalDiffAssetCollectionMatcher,
      ProposalDiffImageMatcher,
      ProposalDiffFileMatcher,
      ProposalDiffReferenceEntityMatcher,
      ProposalDiffReferenceEntityCollectionMatcher,
      ProposalDiffMeasurementMatcher,
    ].find(proposalDiff => proposalDiff.supports(change.attributeType)) || ProposalDiffFallbackMatcher;

  const ProposalDiff = matcher.render();

  return <ProposalDiff accessor={accessor} change={change} className={className} {...rest} />;
};

export {ProposalChange};
