import React, {FC} from 'react';

const __ = require('oro/translator');

const AxisError: FC = () => {
  return (
    <div className="NoRateWindow">
      <span className="NoRateIconWindow" />
      <span className="AxisErrorMessage">
        {__(`akeneo_data_quality_insights.product_evaluation.messages.error.axis_error`)}
      </span>
    </div>
  );
};

export {AxisError};
