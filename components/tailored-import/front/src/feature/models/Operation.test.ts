import {getDefaultOperation} from './Operation';

test('it can get the default operation for each type', () => {
  expect(getDefaultOperation('clean_html_tags')).toEqual({uuid: expect.any(String), type: 'clean_html_tags'});
  expect(getDefaultOperation('split')).toEqual({uuid: expect.any(String), type: 'split', separator: ','});
  expect(getDefaultOperation('simple_select_replacement')).toEqual({
    uuid: expect.any(String),
    type: 'simple_select_replacement',
    mapping: {},
  });
  expect(getDefaultOperation('multi_select_replacement')).toEqual({
    uuid: expect.any(String),
    type: 'multi_select_replacement',
    mapping: {},
  });
  expect(getDefaultOperation('category_replacement')).toEqual({
    uuid: expect.any(String),
    type: 'category_replacement',
    mapping: {},
  });
  // @ts-expect-error invalid type
  expect(() => getDefaultOperation('unknown')).toThrowError('Invalid operation type: "unknown"');
});
