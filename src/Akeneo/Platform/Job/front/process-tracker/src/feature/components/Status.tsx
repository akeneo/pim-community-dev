import React from 'react';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {JobExecutionTracking} from '../models/JobExecutionDetail';
import {JobExecutionStatus} from './JobExecutionStatus';

const Container = styled.div`
  margin-top: 8px;
  display: flex;
  align-items: center;
  gap: 5px;
`;

const Status = ({tracking}: {tracking: JobExecutionTracking}) => {
  const translate = useTranslate();

  return (
    <Container>
      {translate('pim_common.status')}
      <JobExecutionStatus
        data-testid="job-status"
        status={tracking.status}
        currentStep={tracking.currentStep}
        totalSteps={tracking.totalSteps}
        hasWarning={tracking.warning}
        hasError={tracking.error}
      />
    </Container>
  );
};

export {Status};
