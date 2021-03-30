import React from "react";
import { diffChars } from "diff";

type ProposalDiffStringProps = {
  accessor: 'before_data' | 'after_data',
  change: {
    before_data: string | null;
    after_data: string | null;
  }
}

const ProposalDiffString: React.FC<ProposalDiffStringProps> = ({
    accessor,
    change,
    ...rest
  }) => {
  return <span {...rest}>
    {diffChars(change.before_data || '', change.after_data || '').map((change, i) => {
      if (accessor === 'before_data' && change.removed) {
        return <del key={i}>{change.value}</del>
      }
      if (accessor === 'after_data' && change.added) {
        return <ins key={i}>{change.value}</ins>
      }
      if ((accessor === 'before_data' && !change.added) || (accessor === 'after_data' && !change.removed)) {
        return change.value
      }
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
