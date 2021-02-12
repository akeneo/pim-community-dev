import React from 'react';
import {formatSecondsIntl} from 'pimui/js/intl-duration';
import styled from 'styled-components';
import {Level, ProgressBar} from 'akeneo-design-system';
import {StepExecutionTracking} from './models/job-execution';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

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

  return 'primary';
};

const getStepExecutionTrackingPercent = (step: StepExecutionTracking): number | 'indeterminate' => {
  if (step.status === 'STARTING') return 0;

  if (step.status === 'COMPLETED') return 100;

  if (step.totalItems === 0 || !step.isTrackable) {
    switch (step.status) {
      case 'STOPPED':
      case 'FAILED':
      case 'ABANDONED':
        return 100;
      case 'STARTED':
      case 'STOPPING':
      case 'UNKNOWN':
      default:
        return 'indeterminate';
    }
  }

  return (step.processedItems * 100) / step.totalItems;
};

const getStepExecutionTrackingTitle = (step: StepExecutionTracking): string => {
  const translate = useTranslate();

  let key = `batch_jobs.${step.jobName}.${step.stepName}.label`;
  if (translate(key) === key) {
    key = `batch_jobs.default_steps.${step.stepName}`;
  }

  return translate(key);
};

const getStepExecutionTrackingProgressLabel = (jobStatus: string, step: StepExecutionTracking): string => {
  const translate = useTranslate();

  switch (step.status) {
    case 'STARTING':
      return translate('pim_import_export.tracking.not_started');
    case 'STARTED':
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

const JobExecutionProgress = ({jobStatus, steps}: {jobStatus: string, steps: StepExecutionTracking[]}) => {
  return (
    <Container>
      {steps.map((step: StepExecutionTracking, index: number) => (
        <ProgressBar
          key={index}
          title={getStepExecutionTrackingTitle(step)}
          progressLabel={getStepExecutionTrackingProgressLabel(jobStatus, step)}
          level={guessStepExecutionTrackingLevel(step)}
          percent={getStepExecutionTrackingPercent(step)}
          size="large"
        />
      ))}
    </Container>
  );
};

export {JobExecutionProgress};
