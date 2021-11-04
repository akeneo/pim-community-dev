import {JobStatus} from './JobStatus'

type JobExecutionRow = {
  job_execution_id: number;
  job_name: string;
  type: string;
  started_at: string | null;
  username: string | null;
  status: JobStatus;
  warning_count: number;
  error_count: number;
  tracking: {
    current_step: number;
    total_step: number;
  },
};

type JobExecutionTable = {
  rows: JobExecutionRow[];
  matches_count: number;
  total_count: number;
};

export type {JobExecutionTable, JobExecutionRow};
