import React from 'react';
import styled from 'styled-components';
import {Badge, Level, Tooltip} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {JobStatus, isInProgress, isPaused} from '../../models';

const StatusContainer = styled.div`
  display: flex;
  align-items: center;
`;

const badgeLevel = (status: JobStatus, hasError: boolean, hasWarning: boolean): Level => {
  switch (true) {
    case status === 'FAILED' || hasError:
      return 'danger';
    case hasWarning:
      return 'warning';
    case isPaused(status):
      return 'tertiary';
    default:
      return 'primary';
  }
};

type JobExecutionStatusProps = {
  status: JobStatus;
  currentStep: number;
  totalSteps: number;
  hasWarning: boolean;
  hasError: boolean;
};

const JobExecutionStatus = ({
  status,
  currentStep,
  totalSteps,
  hasWarning,
  hasError,
  ...props
}: JobExecutionStatusProps) => {
  const translate = useTranslate();
  const level = badgeLevel(status, hasError, hasWarning);
  const showTooltip = isPaused(status);

  const label = isInProgress(status)
    ? `${translate(`akeneo_job.job_status.${status}`)} ${currentStep}/${totalSteps}`
    : translate(`akeneo_job.job_status.${status}`);

  return (
    <StatusContainer>
      <Badge level={level} {...props}>
        {label}
      </Badge>
      {showTooltip && (
        <Tooltip direction="bottom" iconSize={18}>
          {translate('akeneo_job_process_tracker.tooltip.paused')}
        </Tooltip>
      )}
    </StatusContainer>
  );
};

export {JobExecutionStatus};
