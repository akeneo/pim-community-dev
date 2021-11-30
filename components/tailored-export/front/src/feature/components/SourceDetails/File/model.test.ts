import {isFileSource, FileSource} from './model';

const source: FileSource = {
  uuid: '123',
  code: 'a code',
  type: 'attribute',
  locale: 'fr_FR',
  channel: 'ecommerce',
  operations: {},
  selection: {type: 'path'},
};

test('it validates that something is a file source', () => {
  expect(isFileSource(source)).toEqual(true);

  expect(
    isFileSource({
      ...source,
      operations: {
        default_value: {
          type: 'default_value',
          value: 'a default value',
        },
      },
    })
  ).toEqual(true);

  expect(
    // @ts-expect-error invalid operations
    isFileSource({
      ...source,
      operations: {
        foo: 'bar',
      },
    })
  ).toEqual(false);
});
