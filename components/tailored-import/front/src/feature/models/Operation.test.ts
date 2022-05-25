import {getDefaultOperation} from './Operation';

test('it can get the default operation for each type', () => {
  expect(getDefaultOperation('clean_html_tags')).toEqual({type: 'clean_html_tags'});
  expect(getDefaultOperation('split')).toEqual({type: 'split', separator: ','});
  expect(getDefaultOperation('simple_select_replacement')).toEqual({type: 'simple_select_replacement', mapping: {}});
  expect(getDefaultOperation('multi_select_replacement')).toEqual({type: 'multi_select_replacement', mapping: {}});
  // @ts-expect-error invalid type
  expect(() => getDefaultOperation('unknown')).toThrowError('Invalid operation type: "unknown"');
});
