import React from 'react';

type ProposalDiffFallbackProps = {
  accessor: 'before' | 'after';
  change: {
    before: any;
    after: any;
  };
};

const ProposalDiffFallback: React.FC<ProposalDiffFallbackProps> = ({accessor, change, ...rest}) => {
  return <span {...rest}>{JSON.stringify(change[accessor])}</span>;
};

class ProposalDiffFallbackMatcher {
  static render() {
    return ProposalDiffFallback;
  }
}

export {ProposalDiffFallbackMatcher};
