import React, {FunctionComponent} from 'react';

const __ = require('oro/translator');

interface AxisGradingInProgressProps {}

const AxisGradingInProgress: FunctionComponent<AxisGradingInProgressProps> = () => {
  return (
    <div className="AknDataQualityInsightsEvaluation">
      <span className="gradingInProgressIcon" />
      <span className="gradingInProgressMessage">
        {__(`akeneo_data_quality_insights.product_evaluation.messages.axis_grading_in_progress`)}
      </span>
    </div>
  );
};

export default AxisGradingInProgress;
