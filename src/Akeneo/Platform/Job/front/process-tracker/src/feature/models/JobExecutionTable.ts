import {JobStatus} from './JobStatus';

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
  };
  is_stoppable: boolean;
};

type JobExecutionTable = {
  rows: JobExecutionRow[];
  matches_count: number;
};

const stoppableStatus = ['STARTING', 'STARTED'];
const jobCanBeStopped = (jobExecutionRow: JobExecutionRow): boolean => {
  return jobExecutionRow.is_stoppable && stoppableStatus.includes(jobExecutionRow.status);
};

export {jobCanBeStopped};
export type {JobExecutionTable, JobExecutionRow};
