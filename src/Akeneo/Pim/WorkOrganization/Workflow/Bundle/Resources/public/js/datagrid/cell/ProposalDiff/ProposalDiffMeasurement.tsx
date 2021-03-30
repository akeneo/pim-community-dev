import React from "react";
import { diffChars } from "diff";

type ProposalDiffMeasurementProps = {
  accessor: 'before_data' | 'after_data',
  change: {
    before_data: string | null;
    after_data: string | null;
  }
}

const ProposalDiffMeasurement: React.FC<ProposalDiffMeasurementProps> = ({
  accessor,
  change,
  ...rest
}) => {
  const splitAmountAndUnit = (data: string) => {
    // \u00A0 = NBSP
    const regex = /(?<amount>[^\u00A0]+)\u00A0(?<unit>.+)/
    const found = data.match(regex);
    if (found === null) {
      return {
        amount: '',
        unit: '',
      }
    }
    return {
      amount: found.groups.amount,
      unit: found.groups.unit
    }
  }

  const beforeData = splitAmountAndUnit(change.before_data || '');
  const afterData = splitAmountAndUnit(change.after_data || '');
  const isUnitDiff = beforeData.unit !== afterData.unit;

  return <span {...rest}>
    {diffChars(beforeData.amount, afterData.amount || '').map((change, i) => {
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
    &nbsp;
    {isUnitDiff && accessor === 'before_data' &&
    <del>{beforeData.unit}</del>
    }
    {isUnitDiff && accessor === 'after_data' &&
    <ins>{afterData.unit}</ins>
    }
    {!isUnitDiff && accessor === 'before_data' &&
    beforeData.unit
    }
    {!isUnitDiff && accessor === 'after_data' &&
    afterData.unit
    }
  </span>
}

class ProposalDiffMeasurementMatcher {
  static supports(attributeType: string) {
    return [
      'pim_catalog_metric',   // OK
    ].includes(attributeType);
  }

  static render() {
    return ProposalDiffMeasurement
  }
}


export {ProposalDiffMeasurementMatcher};
