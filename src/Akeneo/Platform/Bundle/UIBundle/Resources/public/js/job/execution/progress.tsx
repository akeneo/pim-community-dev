import React from 'react';
import {formatSecondsIntl} from 'pimui/js/intl-duration';
import styled, {ThemeProvider} from 'styled-components';
import {Level, pimTheme, ProgressBar} from 'akeneo-design-system';
import {ReactView} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';

const __ = require('oro/translator');

type StepExecutionStatus = 'COMPLETED' | 'NOT_STARTED' | 'IN_PROGRESS';
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
}

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
  if (!step.isTrackable) {
    return 'indeterminate';
  }

  if (step.totalItems === 0) {
    switch (step.status) {
      case 'COMPLETED':
        return 100;
      case 'IN_PROGRESS':
      case 'NOT_STARTED':
        return 0;
    }
  }

  return Math.round((step.processedItems * 100) / step.totalItems);
};

const getStepExecutionTrackingTitle = (step: StepExecutionTracking): string => {
  let key = `batch_jobs.${step.jobName}.${step.stepName}.label`;
  if (__(key) === key) {
    key = `batch_jobs.default_steps.${step.stepName}`;
  }

  return __(key);
};

const getStepExecutionTrackingProgressLabel = (step: StepExecutionTracking): string => {
  if (!step.isTrackable) {
    return __('pim_import_export.tracking.untrackable');
  }

  switch (step.status) {
    case 'NOT_STARTED':
      return __('pim_import_export.tracking.not_started');
    case 'COMPLETED':
      return __('pim_import_export.tracking.completed', {duration: formatSecondsIntl(step.duration)});
    case 'IN_PROGRESS':
      return __('pim_import_export.tracking.in_progress', {duration: formatSecondsIntl(step.duration)});
  }
};

class JobExecutionProgress extends ReactView {
  configure () {
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

  remove() {
    this.stopListening();

    return super.remove();
  }
}

export = JobExecutionProgress;
