import React from "react";
import { ProposalChangeFindNewName } from "./Proposal";
import {
  ProposalDiffStringMatcher,
  ProposalDiffStringArrayMatcher,
  ProposalDiffAssetCollectionMatcher,
  ProposalDiffImageMatcher,
  ProposalDiffFallbackMatcher,
  ProposalDiffFileMatcher,
  ProposalDiffReferenceEntityMatcher,
  ProposalDiffReferenceEntityCollectionMatcher, ProposalDiffMeasurementMatcher,
} from "./ProposalDiff";

type ProposalChangeProps = {
  attributeType: string;
  change: ProposalChangeFindNewName;
  accessor: 'before_data' | 'after_data';
}

const ProposalChange: React.FC<ProposalChangeProps> = ({
  change,
  accessor,
  ...rest
}) => {
  const matcher = [
    ProposalDiffStringMatcher,
    ProposalDiffStringArrayMatcher,
    ProposalDiffAssetCollectionMatcher,
    ProposalDiffImageMatcher,
    ProposalDiffFileMatcher,
    ProposalDiffReferenceEntityMatcher,
    ProposalDiffReferenceEntityCollectionMatcher,
    ProposalDiffMeasurementMatcher,
    ProposalDiffFallbackMatcher,
  ].find((proposalDiff) => proposalDiff.supports(change.attributeType));

  const ProposalDiff = matcher.render();

  return <ProposalDiff accessor={accessor} change={change} {...rest}/>
}

export { ProposalChange }
