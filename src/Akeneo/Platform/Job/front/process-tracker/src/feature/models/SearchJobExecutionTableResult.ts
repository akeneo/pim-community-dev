type JobExecutionRow = {};

type SearchJobExecutionTableResult = {
  rows: JobExecutionRow[];
  matches_count: number;
  total_count: number;
};

export type {SearchJobExecutionTableResult};
