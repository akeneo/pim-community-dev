import {Level, ProgressBarPercent} from 'akeneo-design-system';
import {Translate} from '@akeneo-pim-community/shared';
import {formatSecondsIntl} from '../tools/intl-duration';
import {StepStatus} from './StepStatus';
import {JobStatus} from './JobStatus';

type StepExecutionRowTracking = {
  error_count: number;
  warning_count: number;
  is_trackable: boolean;
  processed_items: number;
  total_items: number;
  status: StepStatus;
  duration: number;
};

const getStepExecutionRowTrackingProgressLabel = (
  translate: Translate,
  jobStatus: JobStatus,
  step?: StepExecutionRowTracking
): string => {
  if (undefined === step || step.status === 'STARTING') {
    return translate('akeneo_job_process_tracker.tracking.not_started');
  }

  switch (step.status) {
    case 'STARTED':
      if (!step.is_trackable || 'FAILED' === jobStatus) {
        return translate('akeneo_job_process_tracker.tracking.untrackable');
      }

      if (step.total_items === 0 || step.processed_items === 0) {
        return translate('akeneo_job_process_tracker.tracking.estimating');
      }

      const percentProcessed = (step.processed_items * 100) / step.total_items;
      const durationProjection = Math.round((step.duration * 100) / percentProcessed);
      const durationLeft = durationProjection - step.duration;

      return translate('akeneo_job_process_tracker.tracking.in_progress', {
        duration: formatSecondsIntl(translate, durationLeft),
      });
    case 'ABANDONED':
    case 'COMPLETED':
    case 'FAILED':
    case 'STOPPED':
    case 'STOPPING':
    case 'UNKNOWN':
    default:
      return translate('akeneo_job_process_tracker.tracking.completed', {
        duration: formatSecondsIntl(translate, step.duration),
      });
  }
};

const getStepExecutionRowTrackingLevel = ({warning_count, error_count}: StepExecutionRowTracking): Level => {
  if (0 < warning_count) return 'warning';
  if (0 < error_count) return 'danger';

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

export {getStepExecutionRowTrackingLevel, getStepExecutionRowTrackingProgressLabel, getStepExecutionRowTrackingPercent};
export type {StepExecutionRowTracking};
