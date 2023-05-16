import React from 'react';
import styled from 'styled-components';
import {Level, ProgressBar, ProgressBarPercent} from 'akeneo-design-system';
import {StepExecutionTracking, isPaused} from '../../models';
import {Translate, useTranslate} from '@akeneo-pim-community/shared';
import {formatSecondsIntl} from '../../tools/intl-duration';

const Container = styled.div`
  display: grid;
  grid-auto-flow: column;
  grid-gap: 5px;
  grid-auto-columns: 1fr;
  margin-top: 40px;
`;

const guessStepExecutionTrackingLevel = (step: StepExecutionTracking): Level => {
  if (step.hasError) return 'danger';
  if (step.hasWarning) return 'warning';
  if (isPaused(step.status)) return 'tertiary';

  return 'primary';
};

const getStepExecutionTrackingPercent = (step: StepExecutionTracking): ProgressBarPercent => {
  if (step.status === 'STARTING') return 0;

  if (step.status === 'COMPLETED') return 100;

  if (step.totalItems === 0 || !step.isTrackable) {
    switch (step.status) {
      case 'STOPPED':
      case 'FAILED':
      case 'ABANDONED':
        return 100;
      case 'IN_PROGRESS':
      case 'STOPPING':
      case 'UNKNOWN':
      default:
        return 'indeterminate';
    }
  }

  return (step.processedItems * 100) / step.totalItems;
};

const getStepExecutionTrackingTitle = (translate: Translate, step: StepExecutionTracking): string => {
  let key = `batch_jobs.${step.jobName}.${step.stepName}.label`;
  if (translate(key) === key) {
    key = `batch_jobs.default_steps.${step.stepName}`;
  }

  return translate(key);
};

const getStepExecutionTrackingProgressLabel = (
  translate: Translate,
  jobStatus: string | undefined,
  step: StepExecutionTracking
): string => {
  switch (step.status) {
    case 'STARTING':
      return translate('pim_import_export.tracking.not_started');
    case 'IN_PROGRESS':
      if (!step.isTrackable || 'Failed' === jobStatus) {
        return translate('pim_import_export.tracking.untrackable');
      }

      if (step.totalItems === 0 || step.processedItems === 0) {
        return translate('pim_import_export.tracking.estimating');
      }

      const percentProcessed = (step.processedItems * 100) / step.totalItems;
      const durationProjection = Math.round((step.duration * 100) / percentProcessed);
      const durationLeft = durationProjection - step.duration;

      return translate('pim_import_export.tracking.in_progress', {
        duration: formatSecondsIntl(translate, durationLeft),
      });
    case 'PAUSED':
    case 'PAUSING':
      return translate(`akeneo_job.job_status.${step.status}`);
    case 'ABANDONED':
    case 'COMPLETED':
    case 'FAILED':
    case 'STOPPED':
    case 'STOPPING':
    case 'UNKNOWN':
    default:
      return translate('pim_import_export.tracking.completed', {duration: formatSecondsIntl(translate, step.duration)});
  }
};

const Progress = ({jobStatus, steps}: {jobStatus: string | undefined; steps: StepExecutionTracking[]}) => {
  const translate = useTranslate();

  return (
    <Container>
      {steps.map((step: StepExecutionTracking, index: number) => (
        <ProgressBar
          key={index}
          title={getStepExecutionTrackingTitle(translate, step)}
          progressLabel={getStepExecutionTrackingProgressLabel(translate, jobStatus, step)}
          level={guessStepExecutionTrackingLevel(step)}
          percent={getStepExecutionTrackingPercent(step)}
          size="large"
        />
      ))}
    </Container>
  );
};

export {Progress};
