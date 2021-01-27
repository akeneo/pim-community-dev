import React from 'react';
import {formatSecondsIntl} from 'pimui/js/intl-duration';
import styled, {ThemeProvider} from 'styled-components';
import {Level, pimTheme, ProgressBar} from 'akeneo-design-system';
import {ReactView} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';

const __ = require('oro/translator');

type StepExecutionStatus =
  | 'COMPLETED'
  | 'STARTING'
  | 'STARTED'
  | 'STOPPING'
  | 'STOPPED'
  | 'FAILED'
  | 'ABANDONED'
  | 'UNKNOWN';
type StepExecutionTracking = {
  hasError: boolean;
  hasWarning: boolean;
  isTrackable: boolean;
  jobName: string;
  stepName: string;
  status: StepExecutionStatus;
  duration: number;
  processedItems: number;
  totalItems: number;
};

const Container = styled.div`
  display: grid;
  grid-auto-flow: column;
  grid-gap: 5px;
  grid-auto-columns: 1fr;
`;

const guessStepExecutionTrackingLevel = (step: StepExecutionTracking): Level => {
  if (step.hasError) {
    return 'danger';
  }
  if (step.hasWarning) {
    return 'warning';
  }
  return 'primary';
};

const getStepExecutionTrackingPercent = (step: StepExecutionTracking): number | 'indeterminate' => {
  if (step.status === 'STARTING') {
    return 0;
  }

  if (step.status === 'COMPLETED') {
    return 100;
  }

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
  let key = `batch_jobs.${step.jobName}.${step.stepName}.label`;
  if (__(key) === key) {
    key = `batch_jobs.default_steps.${step.stepName}`;
  }

  return __(key);
};

const getStepExecutionTrackingProgressLabel = (step: StepExecutionTracking): string => {
  switch (step.status) {
    case 'STARTING':
      return __('pim_import_export.tracking.not_started');
    case 'STARTED':
      if (!step.isTrackable) {
        return __('pim_import_export.tracking.untrackable');
      }

      if (step.totalItems === 0 || step.processedItems === 0) {
        return __('pim_import_export.tracking.estimating');
      }

      const percentProcessed = (step.processedItems * 100) / step.totalItems;
      const durationProjection = Math.round((step.duration * 100) / percentProcessed);
      const durationLeft = durationProjection - step.duration;
      return __('pim_import_export.tracking.in_progress', {duration: formatSecondsIntl(durationLeft)});
    case 'ABANDONED':
    case 'COMPLETED':
    case 'FAILED':
    case 'STOPPED':
    case 'STOPPING':
    case 'UNKNOWN':
    default:
      return __('pim_import_export.tracking.completed', {duration: formatSecondsIntl(step.duration)});
  }
};

class JobExecutionProgress extends ReactView {
  /* istanbul ignore next */
  configure() {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);

    return super.configure();
  }

  reactElementToMount() {
    const data = this.getRoot().getFormData();

    return (
      <ThemeProvider theme={pimTheme}>
        <Container>
          {data.tracking.steps.map((step: StepExecutionTracking, i: number) => (
            <ProgressBar
              key={i}
              title={getStepExecutionTrackingTitle(step)}
              progressLabel={getStepExecutionTrackingProgressLabel(step)}
              level={guessStepExecutionTrackingLevel(step)}
              percent={getStepExecutionTrackingPercent(step)}
              size="large"
            />
          ))}
        </Container>
      </ThemeProvider>
    );
  }

  /* istanbul ignore next */
  remove() {
    this.stopListening();

    return super.remove();
  }
}

export default JobExecutionProgress;
