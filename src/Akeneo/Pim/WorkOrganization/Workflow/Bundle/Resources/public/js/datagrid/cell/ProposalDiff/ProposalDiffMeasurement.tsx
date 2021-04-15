import React from "react";
import { diffChars } from "diff";

type ProposalDiffMeasurementProps = {
  accessor: 'before' | 'after',
  change: {
    before: string | null;
    after: string | null;
  }
}

const ProposalDiffMeasurement: React.FC<ProposalDiffMeasurementProps> = ({
  accessor,
  change,
  ...rest
}) => {
  const splitAmountAndUnit = (data: string) => {
    const regex = /(?<amount>[^\u00A0]+)\u00A0(?<unit>.+)/; // \u00A0 = NBSP
    const found = data.match(regex);
    if (!found || !found.groups) {
      return {
        amount: '',
        unit: '',
      }
    }
    return {
      amount: found.groups.amount,
      unit: found.groups.unit,
    }
  }

  const beforeData = splitAmountAndUnit(change.before || '');
  const afterData = splitAmountAndUnit(change.after || '');
  const isUnitDiff = beforeData.unit !== afterData.unit;

  return <span {...rest}>
    {diffChars(beforeData.amount, afterData.amount || '').map((change, i) => {
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
    &nbsp;
    {isUnitDiff && accessor === 'before' &&
    <del>{beforeData.unit}</del>
    }
    {isUnitDiff && accessor === 'after' &&
    <ins>{afterData.unit}</ins>
    }
    {!isUnitDiff && accessor === 'before' &&
    beforeData.unit
    }
    {!isUnitDiff && accessor === 'after' &&
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
