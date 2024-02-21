import React, {FC} from 'react';
import QualityBadge from './QualityBadge';

const goodBadge = <QualityBadge label={'good'} />;
const toImproveBadge = <QualityBadge label={'to_improve'} />;
const naBadge = <QualityBadge label={'n_a'} />;

const AttributeOptionQualityBadge: FC<{toImprove: undefined | 'n/a' | boolean}> = ({toImprove}) => {
  if (toImprove === undefined) {
    return null;
  }

  if (toImprove === 'n/a') {
    return naBadge;
  }

  return <>{toImprove ? toImproveBadge : goodBadge}</>;
};

export default AttributeOptionQualityBadge;
