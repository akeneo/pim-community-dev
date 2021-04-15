import React from 'react';
import {diffArrays} from 'diff';
import { ProposalChangeAccessor } from "../ProposalChange";

type ProposalDiffStringArrayProps = {
  accessor: ProposalChangeAccessor;
  change: {
    before: string[] | null;
    after: string[] | null;
  };
};

const ProposalDiffStringArray: React.FC<ProposalDiffStringArrayProps> = ({accessor, change, ...rest}) => {
  const elements: JSX.Element[] = [];
  diffArrays(change.before || [], change.after || []).forEach(change => {
    if (accessor === 'before' && change.removed) {
      change.value.forEach(value => {
        elements.push(<del key={`ProposalDiffStringArray-${elements.length}`}>{value}</del>);
      });
    } else if (accessor === 'after' && change.added) {
      change.value.forEach(value => {
        elements.push(<ins key={`ProposalDiffStringArray-${elements.length}`}>{value}</ins>);
      });
    } else if ((accessor === 'before' && !change.added) || (accessor === 'after' && !change.removed)) {
      change.value.forEach(value => {
        elements.push(<span key={`ProposalDiffStringArray-${elements.length}`}>{value}</span>);
      });
    }
  });

  if (elements.length) {
    const [firstElement, ...otherElements] = elements;
    return (
      <span {...rest}>
        {firstElement}
        {otherElements.reduce((current, previous) => [current, ', ', previous], null)}
      </span>
    );
  }

  return <span {...rest}/>;
};

class ProposalDiffStringArrayMatcher {
  static supports(attributeType: string) {
    return [
      'pim_catalog_multiselect',
      'pim_reference_data_multiselect',
      'pim_catalog_price_collection',
    ].includes(attributeType);
  }

  static render() {
    return ProposalDiffStringArray;
  }
}

export {ProposalDiffStringArrayMatcher};
