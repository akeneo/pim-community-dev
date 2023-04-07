const AVAILABLE_JOB_STATUSES = [
  'ABANDONED',
  'COMPLETED',
  'FAILED',
  'IN_PROGRESS',
  'STARTING',
  'STOPPED',
  'STOPPING',
  'UNKNOWN',
  'PAUSED',
] as const;

type JobStatus = typeof AVAILABLE_JOB_STATUSES[number];

export type {JobStatus};
export {AVAILABLE_JOB_STATUSES};
