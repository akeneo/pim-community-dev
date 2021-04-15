import React from "react";

type ProposalDiffFallbackProps = {
  accessor: 'before' | 'after',
  change: {
    before: any;
    after: any;
  }
}

const ProposalDiffFallback: React.FC<ProposalDiffFallbackProps> = ({
  accessor,
  change,
  ...rest
}) => {
  return <span {...rest} style={{fontFamily: 'monospace'}}>{JSON.stringify(change[accessor])}</span>
  // TODO
}

class ProposalDiffFallbackMatcher {
  static supports(attributeType: string) {
    return true;
  }

  static render() {
    return ProposalDiffFallback
  }
}

export {ProposalDiffFallbackMatcher};
