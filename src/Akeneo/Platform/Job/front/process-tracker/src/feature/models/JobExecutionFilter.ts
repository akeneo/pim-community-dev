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
  user: string[];
  search: string;
  code: string[];
};

const getDefaultJobExecutionFilter = (): JobExecutionFilter => ({
  page: 1,
  size: ITEMS_PER_PAGE,
  sort: {column: 'started_at', direction: 'DESC'},
  type: [],
  status: [],
  user: [],
  search: '',
  code: [],
});

const isDefaultJobExecutionFilter = ({
  page,
  size,
  sort,
  type,
  status,
  search,
  user,
  code,
}: JobExecutionFilter): boolean =>
  1 === page &&
  ITEMS_PER_PAGE === size &&
  'started_at' === sort.column &&
  'DESC' === sort.direction &&
  0 === status.length &&
  0 === type.length &&
  0 === code.length &&
  0 === user.length &&
  '' === search;

const hasOneFilterSet = ({type, status, search, code, user}: JobExecutionFilter): boolean => {
  return 0 < type.length || 0 < status.length || '' !== search || 0 < code.length || 0 < user.length;
};

export type {JobExecutionFilter, JobExecutionFilterSort};
export {getDefaultJobExecutionFilter, isDefaultJobExecutionFilter, hasOneFilterSet};
