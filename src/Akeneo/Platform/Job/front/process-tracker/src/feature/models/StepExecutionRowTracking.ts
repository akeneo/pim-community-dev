import {Level, ProgressBarPercent} from 'akeneo-design-system';
import {StepStatus} from './StepStatus';
import {isPaused} from './JobStatus';

type StepExecutionRowTracking = {
  has_error: boolean;
  warning_count: number;
  is_trackable: boolean;
  processed_items: number;
  total_items: number;
  status: StepStatus;
  duration: number;
};

const getStepExecutionRowTrackingLevel = ({warning_count, has_error, status}: StepExecutionRowTracking): Level => {
  if (has_error) return 'danger';
  if (0 < warning_count) return 'warning';
  if (isPaused(status)) return 'tertiary';

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
      case 'IN_PROGRESS':
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
