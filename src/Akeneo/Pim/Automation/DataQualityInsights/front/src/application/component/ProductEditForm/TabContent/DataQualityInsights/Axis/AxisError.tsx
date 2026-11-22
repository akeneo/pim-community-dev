import React, {FC} from 'react';

import {translate as __} from '@akeneo-pim-community/shared';

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
