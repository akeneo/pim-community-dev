import {JobStatus} from './JobStatus';

const ITEMS_PER_PAGE = 25;

type JobExecutionFilterSort = {
  column: string;
  direction: 'ASC' | 'DESC';
};

type JobExecutionFilter = {
  page: number;
  size: number;
  sort: JobExecutionFilterSort;
  automation: null | boolean;
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
  automation: null,
  type: [],
  status: [],
  user: [],
  search: '',
  code: [],
});

const isDefaultJobExecutionFilter = ({page, size, type, status, search, user, code}: JobExecutionFilter): boolean =>
  1 === page &&
  ITEMS_PER_PAGE === size &&
  0 === status.length &&
  0 === type.length &&
  0 === code.length &&
  0 === user.length &&
  '' === search;

export type {JobExecutionFilter, JobExecutionFilterSort};
export {getDefaultJobExecutionFilter, isDefaultJobExecutionFilter};
