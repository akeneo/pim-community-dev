import React, {FC} from 'react';
import QualityBadge from '../../Common/QualityBadge';
import {SpellcheckEvaluation} from '../../../../infrastructure/hooks/AttributeEditForm/useSpellcheckEvaluationState';

const goodBadge = <QualityBadge label={'good'} />;
const toImproveBadge = <QualityBadge label={'to_improve'} />;
const naBadge = <QualityBadge label={'n_a'} />;

const AttributeOptionQualityBadge: FC<{option: string; evaluation: SpellcheckEvaluation}> = ({option, evaluation}) => {
  console.log('test');
  if (!evaluation.options[option]) {
    return naBadge;
  }

  const isToImprove = evaluation.options[option].toImprove > 0 || false;

  return <>{isToImprove ? toImproveBadge : goodBadge}</>;
};

export default AttributeOptionQualityBadge;
