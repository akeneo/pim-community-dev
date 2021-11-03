type JobExecutionRow = {
  job_execution_id: number;
  job_name: string;
  type: string;
  start_at: string | null;
  username: string | null;
  status: string;
  warning_count: number;
};

type JobExecutionTable = {
  rows: JobExecutionRow[];
  matches_count: number;
  total_count: number;
};

export type {JobExecutionTable, JobExecutionRow};
