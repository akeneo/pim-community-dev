const AVAILABLE_STEP_STATUSES = [
  'ABANDONED',
  'COMPLETED',
  'FAILED',
  'STARTED',
  'STARTING',
  'STOPPED',
  'STOPPING',
  'UNKNOWN',
] as const;

type StepStatus = typeof AVAILABLE_STEP_STATUSES[number];

export type {StepStatus};
