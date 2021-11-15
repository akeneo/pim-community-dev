import {getDefaultJobExecutionFilter, isDefaultJobExecutionFilter} from './JobExecutionFilter';

test('it can tell if the given filter is the default Job execution filter', () => {
  expect(isDefaultJobExecutionFilter(getDefaultJobExecutionFilter())).toEqual(true);
  expect(
    isDefaultJobExecutionFilter({
      size: 25,
      page: 2,
      status: [],
      type: [],
    })
  ).toEqual(false);
  expect(
    isDefaultJobExecutionFilter({
      size: 25,
      page: 1,
      status: ['ABANDONED'],
      type: [],
    })
  ).toEqual(false);
  expect(
    isDefaultJobExecutionFilter({
      size: 25,
      page: 1,
      status: [],
      type: ['import'],
    })
  ).toEqual(false);
});
