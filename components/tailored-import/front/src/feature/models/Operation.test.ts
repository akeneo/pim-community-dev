import {getDefaultOperation} from './Operation';

test('it can get the default operation for each type', () => {
  expect(getDefaultOperation('clean_html')).toEqual({
    uuid: expect.any(String),
    modes: ['remove', 'decode'],
    type: 'clean_html',
  });
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
  expect(getDefaultOperation('categories_replacement')).toEqual({
    uuid: expect.any(String),
    type: 'categories_replacement',
    mapping: {},
  });
  expect(getDefaultOperation('family_replacement')).toEqual({
    uuid: expect.any(String),
    type: 'family_replacement',
    mapping: {},
  });
  expect(getDefaultOperation('change_case')).toEqual({
    uuid: expect.any(String),
    type: 'change_case',
    mode: 'uppercase',
  });
  expect(getDefaultOperation('remove_whitespace')).toEqual({
    uuid: expect.any(String),
    type: 'remove_whitespace',
    modes: ['trim'],
  });
  expect(getDefaultOperation('simple_reference_entity_replacement')).toEqual({
    uuid: expect.any(String),
    type: 'simple_reference_entity_replacement',
    mapping: {},
  });
  expect(getDefaultOperation('multi_reference_entity_replacement')).toEqual({
    uuid: expect.any(String),
    type: 'multi_reference_entity_replacement',
    mapping: {},
  });

  // @ts-expect-error invalid type
  expect(() => getDefaultOperation('unknown')).toThrowError('Invalid operation type: "unknown"');
});
