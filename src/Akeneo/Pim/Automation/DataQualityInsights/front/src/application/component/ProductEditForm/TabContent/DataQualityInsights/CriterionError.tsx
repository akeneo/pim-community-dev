import React, {FunctionComponent} from 'react';
import styled from "styled-components";

const __ = require('oro/translator');

interface CriterionErrorProps {

}
const Message = styled.div`
  line-height: normal;
`;

const CriterionError: FunctionComponent<CriterionErrorProps> = () => {
  return (
    <li className="AknVerticalList-item">
      <Message>{__(`akeneo_data_quality_insights.product_evaluation.messages.error.no_evaluation_for_axis`)}</Message>
    </li>
  );
};

export default CriterionError;
