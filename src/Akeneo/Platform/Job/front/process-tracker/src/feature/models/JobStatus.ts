const AVAILABLE_JOB_STATUSES = [
  'ABANDONED',
  'COMPLETED',
  'FAILED',
  'STARTED',
  'STARTING',
  'STOPPED',
  'STOPPING',
  'UNKNOWN',
] as const;

type JobStatus = typeof AVAILABLE_JOB_STATUSES[number];

export type {JobStatus};
export {AVAILABLE_JOB_STATUSES};
