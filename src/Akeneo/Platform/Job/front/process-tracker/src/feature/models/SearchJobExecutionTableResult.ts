type JobExecutionRow = {};

type SearchJobExecutionTableResult = {
  items: JobExecutionRow[];
  matches_count: number;
  total_count: number;
};

export type {SearchJobExecutionTableResult};
