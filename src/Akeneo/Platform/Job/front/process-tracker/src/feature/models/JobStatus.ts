const AVAILABLE_JOB_STATUSES = [
  'ABANDONED',
  'COMPLETED',
  'FAILED',
  'IN_PROGRESS',
  'STARTING',
  'STOPPED',
  'STOPPING',
  'UNKNOWN',
  'PAUSING',
  'PAUSED',
] as const;

type JobStatus = typeof AVAILABLE_JOB_STATUSES[number];

const isPaused = (status: JobStatus): boolean => ['PAUSED', 'PAUSING'].includes(status);

const isInProgress = (status: JobStatus): boolean => ['STARTING', 'IN_PROGRESS'].includes(status);

export type {JobStatus};
export {AVAILABLE_JOB_STATUSES, isPaused, isInProgress};
