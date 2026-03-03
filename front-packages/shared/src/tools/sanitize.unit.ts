import {sanitize} from './sanitize';

test('I remove spaces', () => {
  expect(sanitize('a code with spaces')).toEqual('acodewithspaces');
});

test('I replace not alphanumeric characters by underscore', () => {
  expect(sanitize('a.code-with,5;forbidden~character!')).toEqual('a_code_with_5_forbidden_character_');
});
