import {createQueryParam} from './queryParam';

test('it returns empty query parameter when no parameter is given', () => {
  expect(createQueryParam()).toEqual('');
});

test('it returns query string from parameter', () => {
  expect(createQueryParam({})).toEqual('');
  expect(createQueryParam({key1: 'value1'})).toEqual('?key1=value1');
  expect(createQueryParam({key1: 'value1', key2: 'value2'})).toEqual('?key1=value1&key2=value2');
  expect(createQueryParam({key1: 'value1', key2: ['value2']})).toEqual('?key1=value1&key2[]=value2');
  expect(createQueryParam({key1: 'value1', key2: ['value2', 'value3']})).toEqual(
    '?key1=value1&key2[]=value2&key2[]=value3'
  );
});
