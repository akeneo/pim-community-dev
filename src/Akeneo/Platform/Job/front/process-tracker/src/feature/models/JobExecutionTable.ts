import {Translate} from '@akeneo-pim-community/shared';
import {formatSecondsIntl} from '../tools/intl-duration';
import {JobStatus} from './JobStatus';
import {StepExecutionRowTracking} from './StepExecutionRowTracking';

type JobExecutionRowTracking = {
  current_step: number;
  total_step: number;
  steps: StepExecutionRowTracking[];
};

type JobExecutionRow = {
  job_execution_id: number;
  job_name: string;
  automation: null | boolean;
  type: string;
  started_at: string | null;
  username: string | null;
  status: JobStatus;
  warning_count: number;
  has_error: boolean;
  tracking: JobExecutionRowTracking;
  is_stoppable: boolean;
};

type JobExecutionTable = {
  rows: JobExecutionRow[];
  matches_count: number;
};

const STOPPABLE_STATUS: JobStatus[] = ['STARTING', 'IN_PROGRESS'];
const jobCanBeStopped = (jobExecutionRow: JobExecutionRow): boolean =>
  jobExecutionRow.is_stoppable && STOPPABLE_STATUS.includes(jobExecutionRow.status);

const JOB_TYPES_WITH_ACL = {
  import: 'pim_importexport_import_execution_show',
  export: 'pim_importexport_export_execution_show',
};

const canShowJobExecutionDetail = (isGranted: (acl: string) => boolean, jobExecutionRow: JobExecutionRow): boolean =>
  jobExecutionRow.type in JOB_TYPES_WITH_ACL ? isGranted(JOB_TYPES_WITH_ACL[jobExecutionRow.type]) : true;

const getJobExecutionRowTrackingProgressLabel = (translate: Translate, jobExecutionRow: JobExecutionRow): string => {
  const step = jobExecutionRow.tracking.steps[jobExecutionRow.tracking.current_step - 1] ?? null;

  if (null === step || 'STARTING' === step.status) {
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

export {jobCanBeStopped, canShowJobExecutionDetail, getJobExecutionRowTrackingProgressLabel};
export type {JobExecutionTable, JobExecutionRow};
