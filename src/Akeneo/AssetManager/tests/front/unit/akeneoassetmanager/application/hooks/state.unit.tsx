'use strict';

import '@testing-library/jest-dom/extend-expect';
import {renderHook, act} from '@testing-library/react-hooks';
import {useStoredState} from 'akeneoassetmanager/application/hooks/state';

describe('Test stored state hooks', () => {
  beforeEach(() => {
    localStorage.clear();
  });

  test('It store a state in the local storage', async () => {
    const {result} = renderHook(() => useStoredState('foo', null));
    let [value, setValue] = result.current;
    expect(value).toEqual(null);

    act(() => {
      setValue('new_value');
    });

    let [value, setValue] = result.current;
    expect(value).toEqual('new_value');
    expect(localStorage.getItem('foo')).toEqual(JSON.stringify('new_value'));
  });

  test('It retreive a state from the local storage', async () => {
    localStorage.setItem('foo', JSON.stringify('stored_value'));

    const {result} = renderHook(() => useStoredState('foo', null));
    let [value, setValue] = result.current;
    expect(value).toEqual('stored_value');
  });

  test('It returns the default value if the storage is not readable', async () => {
    // https://github.com/facebook/jest/issues/6798
    jest.spyOn(localStorage.__proto__, 'getItem').mockImplementationOnce(key => {
      throw new Error();
    });

    const {result} = renderHook(() => useStoredState('my_key', 'foo'));
    let [value, setValue] = result.current;
    expect(value).toEqual('foo');
  });

  test('It logs an error if the storage is not writable', async () => {
    // https://github.com/facebook/jest/issues/6798
    jest.spyOn(localStorage.__proto__, 'setItem').mockImplementationOnce((key, value) => {
      throw new Error();
    });
    const logger = jest.spyOn(console, 'error').mockImplementation(() => {});

    const {result} = renderHook(() => useStoredState('my_key', 'foo'));
    let [value, setValue] = result.current;
    act(() => {
      setValue('bar');
    });
    expect(logger).toHaveBeenCalled();
  });

  test('It switch between items when the key changes', async () => {
    localStorage.setItem('foo', JSON.stringify('foo_value'));
    localStorage.setItem('bar', JSON.stringify('bar_value'));

    let key = 'foo';
    const {result, rerender} = renderHook(() => useStoredState(key, null));
    let [value, setValue] = result.current;
    expect(value).toEqual('foo_value');

    key = 'bar';
    rerender();

    let [value, setValue] = result.current;
    expect(value).toEqual('bar_value');
  });
});
