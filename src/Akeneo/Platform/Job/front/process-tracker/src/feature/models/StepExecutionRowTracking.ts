import {Level, ProgressBarPercent} from 'akeneo-design-system';
import {StepStatus} from './StepStatus';

type StepExecutionRowTracking = {
  has_error: boolean;
  has_warning: boolean;
  is_trackable: boolean;
  processed_items: number;
  total_items: number;
  status: StepStatus;
};

const getStepExecutionRowTrackingLevel = (step: StepExecutionRowTracking): Level => {
  if (step.has_error) return 'danger';
  if (step.has_warning) return 'warning';

  return 'primary';
};

const getStepExecutionRowTrackingPercent = (step: StepExecutionRowTracking): ProgressBarPercent => {
  if (step.status === 'STARTING') return 0;

  if (step.status === 'COMPLETED') return 100;

  if (step.total_items === 0 || !step.is_trackable) {
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

  return (step.processed_items * 100) / step.total_items;
};

export {getStepExecutionRowTrackingLevel, getStepExecutionRowTrackingPercent};
export type {StepExecutionRowTracking};
