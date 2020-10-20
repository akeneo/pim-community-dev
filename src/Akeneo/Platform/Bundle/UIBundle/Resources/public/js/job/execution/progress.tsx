import ReactDOM from 'react-dom';
import React from 'react';
import BaseView = require('pimui/js/view/base');
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

// TODO replace by DSM component when finished
type Level = 'primary' | 'secondary' | 'tertiary' | 'warning' | 'danger';
type ProgressBarSize = 'small' | 'large';
type ProgressBarProps = {
  level: Level;
  percent: number | 'indeterminate';
  light?: boolean;
  title?: string;
  progressLabel?: string;
  size?: ProgressBarSize;
};

const ProgressBar = (props: ProgressBarProps) => {
  return (
    <div>
      <div>{props.title}</div>
      <div>{props.progressLabel}</div>
      <div>{props.percent}%</div>
    </div>
  )
};

const guessStepExecutionTrackingLevel = (step: StepExecutionTracking): Level => {
  if (step.hasError) {
    return 'danger';
  }
  if (step.hasWarning) {
    return 'warning';
  }
  return 'primary';
};

const computeStepExecutionTrackingPercent = (step: StepExecutionTracking): number => {
  if (step.totalItems === 0) {
    switch (step.status) {
      case 'COMPLETED':
        return 100;
      case 'IN_PROGRESS':
      case 'NOT_STARTED':
        return 0;
    }
  }

  return Math.round((step.processedItems * 100) / step.totalItems)
};

const getStepExecutionTrackingTitle = (step: StepExecutionTracking): string => {
  let key = 'batch_jobs.' + step.jobName + '.' + step.stepName + '.label';
  if (__(key) === key) {
      key = 'batch_jobs.default_steps.' + step.stepName;
  }

  return __(key);
};

const secondsToIntlDuration = (seconds: number): string => {

};

const guessStepExecutionTrackingProgressLabel = (step: StepExecutionTracking): string => {
  switch (step.status) {
      case 'NOT_STARTED':
        return __('pim_import_export.tracking.eta.not_started');
      case 'COMPLETED':
        return __('pim_import_export.tracking.eta.completed', {duration: secondsToIntlDuration(step.duration)});

      // case 'COMPLETED':
      //   return 100;
      // case 'IN_PROGRESS':
      //   return 0;
  }
};

class JobExecutionProgress extends BaseView {
  render() {
    const data = this.getRoot().getFormData();
    console.log(data);
    ReactDOM.render(
      <div>
        {data.tracking.steps.map((step: StepExecutionTracking, i: number) => (
          <ProgressBar
            key={i}
            title={getStepExecutionTrackingTitle(step)}
            progressLabel={guessStepExecutionTrackingProgressLabel(step)}
            level={guessStepExecutionTrackingLevel(step)}
            percent={computeStepExecutionTrackingPercent(step)}
          />
        ))}
      </div>,
      this.el
    );
    return this;
  }

  remove() {
    ReactDOM.unmountComponentAtNode(this.el);

    return super.remove();
  }
}

export = JobExecutionProgress;
