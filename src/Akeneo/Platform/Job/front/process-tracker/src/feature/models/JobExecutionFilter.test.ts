import {getDefaultJobExecutionFilter, isDefaultJobExecutionFilter} from './JobExecutionFilter';

test('it can tell if the given filter is the default Job execution filter', () => {
  expect(isDefaultJobExecutionFilter(getDefaultJobExecutionFilter())).toEqual(true);
  expect(
    isDefaultJobExecutionFilter({
      ...getDefaultJobExecutionFilter(),
      size: 24,
    })
  ).toEqual(false);
  expect(
    isDefaultJobExecutionFilter({
      ...getDefaultJobExecutionFilter(),
      page: 2,
    })
  ).toEqual(false);
  expect(
    isDefaultJobExecutionFilter({
      ...getDefaultJobExecutionFilter(),
      status: ['ABANDONED'],
    })
  ).toEqual(false);
  expect(
    isDefaultJobExecutionFilter({
      ...getDefaultJobExecutionFilter(),
      type: ['import'],
    })
  ).toEqual(false);
  expect(
    isDefaultJobExecutionFilter({
      ...getDefaultJobExecutionFilter(),
      user: ['admin'],
    })
  ).toEqual(false);
  expect(
    isDefaultJobExecutionFilter({
      ...getDefaultJobExecutionFilter(),
      search: 'My search',
    })
  ).toEqual(false);
  expect(
    isDefaultJobExecutionFilter({
      ...getDefaultJobExecutionFilter(),
      code: ['csv_product_export'],
    })
  ).toEqual(false);
});

test('it did not care about the sort to tell if the given filter is the default Job execution filter', () => {
  expect(
    isDefaultJobExecutionFilter({
      ...getDefaultJobExecutionFilter(),
      sort: {
        column: 'status',
        direction: 'DESC',
      },
    })
  ).toEqual(true);

  expect(
    isDefaultJobExecutionFilter({
      ...getDefaultJobExecutionFilter(),
      sort: {
        column: 'started_at',
        direction: 'ASC',
      },
    })
  ).toEqual(true);
});
