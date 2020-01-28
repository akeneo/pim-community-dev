import React, {FunctionComponent} from 'react';

const __ = require('oro/translator');

interface AxisErrorProps {

}

const AxisError: FunctionComponent<AxisErrorProps> = () => {

  return (
      <div className="NoRateWindow">
        <span className="NoRateIconWindow"></span>
        <span className="CriterionErrorMessage">{__(`akeneo_data_quality_insights.product_evaluation.messages.error.no_evaluation_for_axis`)}</span>
      </div>
  );
};

export default AxisError;
