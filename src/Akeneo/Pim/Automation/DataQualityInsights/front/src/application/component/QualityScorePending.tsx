import React from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Badge} from 'akeneo-design-system';

const QualityScorePending = () => {
  const translate = useTranslate();

  return (
    <Badge level="tertiary" data-testid="quality-score-pending">
      {translate('akeneo_data_quality_insights.quality_score.pending')}
    </Badge>
  );
};

export {QualityScorePending};
