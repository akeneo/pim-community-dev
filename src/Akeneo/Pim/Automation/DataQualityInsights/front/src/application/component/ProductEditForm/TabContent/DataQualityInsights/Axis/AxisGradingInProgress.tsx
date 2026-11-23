import React, {FC} from 'react';

import {translate as __} from '@akeneo-pim-community/shared';

const AxisGradingInProgress: FC = () => {
  return (
    <div className="AknDataQualityInsightsEvaluation">
      <span className="gradingInProgressIcon" />
      <span className="gradingInProgressMessage">
        {__(`akeneo_data_quality_insights.product_evaluation.messages.axis_grading_in_progress`)}
      </span>
    </div>
  );
};

export {AxisGradingInProgress};
