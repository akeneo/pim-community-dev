import React, {FunctionComponent} from 'react';

const __ = require('oro/translator');

interface AxisEvaluationSuccessProps {
  axis: string;
}


const AxisEvaluationSuccess: FunctionComponent<AxisEvaluationSuccessProps> = ({axis}) => {
  return (
    <div className="AxisEvaluationSuccessContainer">
      <div className="AxisEvaluationSuccessIllustration"/>
      <p>{__(`akeneo_data_quality_insights.product_evaluation.axis.${axis}.success`)}</p>
    </div>
  );
};

export default AxisEvaluationSuccess;
