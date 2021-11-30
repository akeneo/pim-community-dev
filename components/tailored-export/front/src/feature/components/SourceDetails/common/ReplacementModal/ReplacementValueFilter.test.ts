import {getDefaultReplacementValueFilter, isDefaultReplacementValueFilter} from './ReplacementValueFilter';

test('it can tell if the given filter is the default Replacement value filter', () => {
  expect(isDefaultReplacementValueFilter(getDefaultReplacementValueFilter())).toEqual(true);
  expect(
    isDefaultReplacementValueFilter({
      searchValue: 'f',
      page: 1,
      codesToInclude: null,
      codesToExclude: null,
    })
  ).toEqual(false);
  expect(
    isDefaultReplacementValueFilter({
      searchValue: '',
      page: 1,
      codesToInclude: ['nice'],
      codesToExclude: null,
    })
  ).toEqual(false);
});
