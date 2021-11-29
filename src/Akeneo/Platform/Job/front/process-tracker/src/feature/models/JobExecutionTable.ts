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

const STOPPABLE_STATUS = ['STARTING', 'STARTED'];
const jobCanBeStopped = (jobExecutionRow: JobExecutionRow): boolean =>
  jobExecutionRow.is_stoppable && STOPPABLE_STATUS.includes(jobExecutionRow.status);

const RESTRICTED_JOB_TYPES = ['import', 'export'];
const canShowJobExecutionDetail = (isGranted: (acl: string) => boolean, jobExecutionRow: JobExecutionRow) => {
  if (RESTRICTED_JOB_TYPES.includes(jobExecutionRow.type)) {
    return isGranted(`pim_importexport_${jobExecutionRow.type}_execution_show`);
  }

  return true;
};

export {jobCanBeStopped, canShowJobExecutionDetail};
export type {JobExecutionTable, JobExecutionRow};
