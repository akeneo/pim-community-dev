import React, {FunctionComponent} from 'react';
import styled from 'styled-components';

const __ = require('oro/translator');

interface AxisEvaluationSuccessProps {
  axis: string;
}

const ILLUSTRATION_URL = '/bundles/akeneodataqualityinsights/images/Success.svg';

const Container = styled.div`
  font-size: 13px;
  max-width: 400px;
  margin: 0 auto;
  text-align: center;
`;

const Illustration = styled.div`
  display: inline-block;
  background-image: url(${ILLUSTRATION_URL}); 
  background-repeat: no-repeat;
  background-position: center center;
  height: 150px;
  width: 150px;
  margin: 0;
  background-size: 100% auto;
`;

const AxisEvaluationSuccess: FunctionComponent<AxisEvaluationSuccessProps> = ({axis}) => {
  return (
    <Container>
      <Illustration />
      <p>{__(`akeneo_data_quality_insights.product_evaluation.axis.${axis}.success`)}</p>
    </Container>
  );
};

export default AxisEvaluationSuccess;
