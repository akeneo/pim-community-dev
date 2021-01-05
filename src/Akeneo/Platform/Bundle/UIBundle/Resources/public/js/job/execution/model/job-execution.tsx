type StepExecutionStatus =
  | 'COMPLETED'
  | 'STARTING'
  | 'STARTED'
  | 'STOPPING'
  | 'STOPPED'
  | 'FAILED'
  | 'ABANDONED'
  | 'UNKNOWN';

type StepExecutionTracking = {
  hasError: boolean;
  hasWarning: boolean;
  isTrackable: boolean;
  jobName: string;
  stepName: string;
  status: StepExecutionStatus;
  duration: number;
  processedItems: number;
  totalItems: number;
};

type JobStatus = 'COMPLETED' | 'STARTING' | 'STARTED' | 'STOPPING' | 'STOPPED' | 'FAILED' | 'ABANDONED' | 'UNKNOWN';

type JobExecutionTracking = {
  error: boolean;
  warning: boolean;
  status: JobStatus;
  currentStep: number;
  totalSteps: number;
  steps: StepExecutionTracking[];
};

type JobInstance = {
  label: string;
  code: string;
  type: string;
};

type JobExecution = {
  jobInstance: JobInstance;
  tracking: JobExecutionTracking;
  isStoppable: boolean;
  meta: {
    id: string;
    logExists: boolean;
    archives: Record<
      string,
      {
        label: string;
        files: Record<string, string>;
      }
    >;
  };
};

export type {JobExecution, JobExecutionTracking, JobInstance, StepExecutionTracking};
