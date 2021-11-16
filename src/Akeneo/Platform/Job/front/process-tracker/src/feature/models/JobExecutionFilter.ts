import {JobStatus} from './JobStatus';

const ITEMS_PER_PAGE = 25;

type JobExecutionFilter = {
  page: number;
  size: number;
  type: string[];
  status: JobStatus[];
  search: string;
};

const getDefaultJobExecutionFilter = () => ({
  page: 1,
  size: ITEMS_PER_PAGE,
  type: [],
  status: [],
  search: '',
});

const isDefaultJobExecutionFilter = ({page, size, type, status, search}: JobExecutionFilter): boolean =>
  1 === page && ITEMS_PER_PAGE === size && 0 === status.length && 0 === type.length && search === '';

export type {JobExecutionFilter};
export {getDefaultJobExecutionFilter, isDefaultJobExecutionFilter};
