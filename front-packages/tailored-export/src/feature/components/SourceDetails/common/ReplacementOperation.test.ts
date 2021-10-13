import {
  filterEmptyValues,
  isReplacementOperation,
  getDefaultReplacementOperation,
  isDefaultReplacementOperation,
} from './ReplacementOperation';

test('it can tell if something is a Replacement operation', () => {
  expect(isReplacementOperation({})).toEqual(false);
  expect(isReplacementOperation(undefined)).toEqual(false);
  expect(isReplacementOperation({type: 'foo'})).toEqual(false);
  expect(isReplacementOperation({type: 'replacement'})).toEqual(false);
  expect(isReplacementOperation({type: 'replacement', mapping: true})).toEqual(false);
  expect(isReplacementOperation({type: 'replacement', mapping: {}})).toEqual(true);
  expect(
    isReplacementOperation({
      type: 'replacement',
      mapping: {
        foo: 'bar',
        bar: 'baz',
      },
    })
  ).toEqual(true);
});

test('it can tell if the given operation is the default Replacement operation', () => {
  expect(isDefaultReplacementOperation(getDefaultReplacementOperation())).toEqual(true);
  expect(
    isDefaultReplacementOperation({
      type: 'replacement',
      mapping: {
        foo: 'bar',
        bar: 'baz',
      },
    })
  ).toEqual(false);
});

test('it can filter out empty replacement values', () => {
  const values = {
    empty: '',
    foo: 'bar',
    bar: 'baz',
    anotherEmpty: '',
  };

  expect(filterEmptyValues(values)).toEqual({
    foo: 'bar',
    bar: 'baz',
  });
});
