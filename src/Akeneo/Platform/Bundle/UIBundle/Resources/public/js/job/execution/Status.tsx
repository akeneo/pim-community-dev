import React from 'react';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {default as JobExecutionStatusBadge} from 'pimimportexport/js/JobExecutionStatus';
import {JobExecutionTracking} from './models';

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
      <JobExecutionStatusBadge
        data-test-id="job-status"
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
