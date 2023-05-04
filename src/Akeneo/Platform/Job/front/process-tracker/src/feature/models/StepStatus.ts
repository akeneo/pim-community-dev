import {Translate} from '@akeneo-pim-community/shared';

type StepStatus =
  | 'ABANDONED'
  | 'COMPLETED'
  | 'FAILED'
  | 'IN_PROGRESS'
  | 'STARTING'
  | 'STOPPED'
  | 'STOPPING'
  | 'UNKNOWN'
  | 'PAUSED'
  | 'PAUSING';

const isStepPaused = (translate: Translate, stepStatus: string) =>
  [translate('akeneo_job.job_status.PAUSING'), translate('akeneo_job.job_status.PAUSED')].includes(stepStatus);

export type {StepStatus};
export {isStepPaused};
