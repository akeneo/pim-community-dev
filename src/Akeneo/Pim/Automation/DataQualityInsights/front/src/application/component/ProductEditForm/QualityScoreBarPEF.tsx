import React from 'react';
import {QualityScoreValue} from '../../../domain';
import {DATA_QUALITY_INSIGHTS_REDIRECT_TO_DQI_TAB} from '../../listener';
import {QualityScorePending} from '../QualityScorePending';
import {QualityScoreBar} from '../QualityScoreBar';

interface Props {
  score: QualityScoreValue | null | 'N/A';
  stacked: boolean;
}

export const QualityScoreBarPEF = ({score, stacked}: Props) => {
  const isPending = score === 'N/A' || score === null;
  const redirectToDqiTab = () => window.dispatchEvent(new CustomEvent(DATA_QUALITY_INSIGHTS_REDIRECT_TO_DQI_TAB));

  if (isPending) {
    return <QualityScorePending />;
  }

  return <QualityScoreBar score={score} stacked={stacked} onClick={redirectToDqiTab} />;
};
