import React from "react";
import { diffChars } from "diff";

type ProposalDiffStringProps = {
  accessor: 'before' | 'after',
  change: {
    before: string | null;
    after: string | null;
  }
}

const ProposalDiffString: React.FC<ProposalDiffStringProps> = ({
    accessor,
    change,
    ...rest
  }) => {
  return <span {...rest}>
    {diffChars(change.before || '', change.after || '').map((change, i) => {
      if (accessor === 'before' && change.removed) {
        return <del key={i}>{change.value}</del>
      }
      if (accessor === 'after' && change.added) {
        return <ins key={i}>{change.value}</ins>
      }
      if ((accessor === 'before' && !change.added) || (accessor === 'after' && !change.removed)) {
        return change.value
      }
      return null;
    })}
  </span>
}

class ProposalDiffStringMatcher {
  static supports(attributeType: string) {
    return [
      'pim_catalog_text', // OK
      'pim_catalog_identifier', // OK
      'pim_catalog_textarea', // OK
      'pim_catalog_simpleselect', // OK
      'pim_reference_data_simpleselect',
      'pim_catalog_date', // OK
      'pim_catalog_number', // OK
      'pim_catalog_boolean', // OK
    ].includes(attributeType);
  }

  static render() {
    return ProposalDiffString
  }
}


export {ProposalDiffStringMatcher};
