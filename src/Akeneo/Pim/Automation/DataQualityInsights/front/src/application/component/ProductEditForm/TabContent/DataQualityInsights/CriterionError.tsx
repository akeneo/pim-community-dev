import React, {FunctionComponent} from 'react';

const __ = require('oro/translator');

interface CriterionErrorProps {

}

const CriterionError: FunctionComponent<CriterionErrorProps> = () => {
  return (
    <li className="AknVerticalList-item">
      <div className="CriterionErrorMessage">{__(`akeneo_data_quality_insights.product_evaluation.messages.error.no_evaluation_for_axis`)}</div>
    </li>
  );
};

export default CriterionError;
