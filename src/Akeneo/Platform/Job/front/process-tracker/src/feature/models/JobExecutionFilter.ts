import {JobStatus} from './JobStatus';

const ITEMS_PER_PAGE = 25;

type JobExecutionFilterSort = {
  column: string;
  direction: string;
};

type JobExecutionFilter = {
  page: number;
  size: number;
  sort: JobExecutionFilterSort;
  type: string[];
  status: JobStatus[];
};

const getDefaultJobExecutionFilter = () => ({
  page: 1,
  size: ITEMS_PER_PAGE,
  sort: {column: 'started_at', direction: 'DESC'},
  type: [],
  status: [],
});

const isDefaultJobExecutionFilter = ({page, size, sort, type, status}: JobExecutionFilter): boolean =>
  1 === page &&
  ITEMS_PER_PAGE === size &&
  'started_at' === sort.column &&
  'DESC' === sort.direction &&
  0 === status.length &&
  0 === type.length;

export type {JobExecutionFilter, JobExecutionFilterSort};
export {getDefaultJobExecutionFilter, isDefaultJobExecutionFilter};
