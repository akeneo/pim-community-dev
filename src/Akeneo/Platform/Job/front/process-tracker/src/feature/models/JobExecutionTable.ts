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

const JOB_TYPES_WITH_ACL = {
  import: 'pim_importexport_import_execution_show',
  export: 'pim_importexport_export_execution_show',
};

const canShowJobExecutionDetail = (isGranted: (acl: string) => boolean, jobExecutionRow: JobExecutionRow): boolean =>
  jobExecutionRow.type in JOB_TYPES_WITH_ACL ? isGranted(JOB_TYPES_WITH_ACL[jobExecutionRow.type]) : true;

export {jobCanBeStopped, canShowJobExecutionDetail};
export type {JobExecutionTable, JobExecutionRow};
