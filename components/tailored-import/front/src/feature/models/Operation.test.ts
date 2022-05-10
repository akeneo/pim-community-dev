import {getDefaultOperation} from './Operation';

test('it can get the default operation for each type', () => {
  expect(getDefaultOperation('clean_html_tags')).toEqual({type: 'clean_html_tags'});
  expect(getDefaultOperation('split')).toEqual({type: 'split', separator: ','});
  // @ts-expect-error invalid type
  expect(() => getDefaultOperation('unknown')).toThrowError('Invalid operation type: "unknown"');
});
