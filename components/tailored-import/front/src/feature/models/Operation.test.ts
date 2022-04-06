import {getDefaultOperation} from './Operation';

test('it can get the default operation for each type', () => {
  expect(getDefaultOperation('clean_html_tags')).toEqual({type: 'clean_html_tags'});
  // @ts-expect-error invalid type
  expect(() => getDefaultOperation('unknown')).toThrowError('Invalid operation type: "unknown"');
});
