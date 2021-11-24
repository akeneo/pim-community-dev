import {getDefaultJobExecutionFilter, isDefaultJobExecutionFilter} from './JobExecutionFilter';

test('it can tell if the given filter is the default Job execution filter', () => {
  expect(isDefaultJobExecutionFilter(getDefaultJobExecutionFilter())).toEqual(true);
  expect(
    isDefaultJobExecutionFilter({
      size: 25,
      page: 2,
      sort: {
        column: 'started_at',
        direction: 'DESC',
      },
      status: [],
      type: [],
      users: [],
      search: '',
    })
  ).toEqual(false);
  expect(
    isDefaultJobExecutionFilter({
      size: 25,
      page: 1,
      sort: {
        column: 'started_at',
        direction: 'DESC',
      },
      status: ['ABANDONED'],
      type: [],
      users: [],
      search: '',
    })
  ).toEqual(false);
  expect(
    isDefaultJobExecutionFilter({
      size: 25,
      page: 1,
      sort: {
        column: 'started_at',
        direction: 'DESC',
      },
      status: [],
      type: ['import'],
      users: [],
      search: '',
    })
  ).toEqual(false);
  expect(
    isDefaultJobExecutionFilter({
      size: 25,
      page: 1,
      sort: {
        column: 'started_at',
        direction: 'DESC',
      },
      status: [],
      type: [],
      users: ['admin'],
      search: '',
    })
  ).toEqual(false);
  expect(
    isDefaultJobExecutionFilter({
      size: 25,
      page: 1,
      sort: {
        column: 'started_at',
        direction: 'DESC',
      },
      status: [],
      type: [],
      users: [],
      search: 'test',
    })
  ).toEqual(false);
});
