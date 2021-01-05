import React from 'react';
import styled from 'styled-components';
import {default as JobExecutionStatusBadge} from 'pimimportexport/js/JobExecutionStatus';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge/src';
import {JobExecutionTracking} from './model/job-execution';

const Label = styled.span`
  display: inline-block;
  vertical-align: top;
  margin: 0 5px 0 0;
`;

const Container = styled.div`
  margin-top: 8px;
`;

const Status = ({tracking}: {tracking: JobExecutionTracking}) => {
  const translate = useTranslate();

  return (
    <Container>
      <Label>{translate('pim_common.status')}</Label>
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
