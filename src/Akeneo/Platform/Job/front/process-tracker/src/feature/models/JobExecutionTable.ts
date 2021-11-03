type JobStatus = 'COMPLETED' | 'STARTING' | 'STARTED' | 'STOPPING' | 'STOPPED' | 'FAILED' | 'ABANDONED' | 'UNKNOWN';

type JobExecutionRow = {
  job_execution_id: number;
  job_name: string;
  type: string;
  started_at: string | null;
  username: string | null;
  status: JobStatus;
  warning_count: number;
};

type JobExecutionTable = {
  rows: JobExecutionRow[];
  matches_count: number;
  total_count: number;
};

export type {JobExecutionTable, JobExecutionRow, JobStatus};
