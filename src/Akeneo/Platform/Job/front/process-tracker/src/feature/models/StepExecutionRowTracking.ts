import {Level, ProgressBarPercent} from 'akeneo-design-system';
import {Translate} from '@akeneo-pim-community/shared';
import {formatSecondsIntl} from '../tools/intl-duration';
import {StepStatus} from './StepStatus';
import {JobExecutionRow} from './JobExecutionTable';

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
  jobExecutionRow: JobExecutionRow,
  step?: StepExecutionRowTracking
): string => {
  if (undefined === step || 'STARTING' === step.status) {
    return translate('akeneo_job_process_tracker.tracking.not_started');
  }

  switch (step.status) {
    case 'IN_PROGRESS':
      if (!step.is_trackable || 'FAILED' === jobExecutionRow.status) {
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
      const totalDuration = jobExecutionRow.tracking.steps.reduce((total, step) => total + step.duration, 0);

      return translate('akeneo_job_process_tracker.tracking.completed', {
        duration: formatSecondsIntl(translate, totalDuration),
      });
  }
};

const getStepExecutionRowTrackingLevel = ({warning_count, error_count}: StepExecutionRowTracking): Level => {
  if (0 < error_count) return 'danger';
  if (0 < warning_count) return 'warning';

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

export {getStepExecutionRowTrackingLevel, getStepExecutionRowTrackingProgressLabel, getStepExecutionRowTrackingPercent};
export type {StepExecutionRowTracking};
