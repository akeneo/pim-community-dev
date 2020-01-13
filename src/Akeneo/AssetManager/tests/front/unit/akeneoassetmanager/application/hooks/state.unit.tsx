'use strict';

import '@testing-library/jest-dom/extend-expect';
import {useStoredState} from 'akeneoassetmanager/application/hooks/state';
import {renderHook, act} from '@testing-library/react-hooks';

describe('Test stored state hooks', () => {
  test('It store a state in the locale storage', async () => {
    const {result} = renderHook(() => useStoredState('my_key', null));
    expect(result.current[0]).toEqual(null);

    act(() => {
      result.current[1]('new_value');
    });

    expect(result.current[0]).toEqual('new_value');
  });

  test('It retreive a state in the locale storage', async () => {
    const {result} = renderHook(() => useStoredState('my_key', null));
    expect(result.current[0]).toEqual('new_value');
  });

  test('It call the after set callback', async () => {
    let listenerValue = 'nice';
    const {result} = renderHook(() =>
      useStoredState('another_key', null, newValue => {
        listenerValue = newValue;
      })
    );
    expect(listenerValue).toEqual('nice');

    act(() => {
      result.current[1]('new_value');
    });

    expect(result.current[0]).toEqual('new_value');
    expect(listenerValue).toEqual('new_value');
  });

  test('Nothing happen if the old value is the same as the new value', async () => {
    let listenerValue = 'nice';
    const {result} = renderHook(() =>
      useStoredState('last_key', 'same_value', newValue => {
        listenerValue = newValue;
      })
    );
    expect(listenerValue).toEqual('nice');

    act(() => {
      result.current[1]('same_value');
    });

    expect(result.current[0]).toEqual('same_value');
    expect(listenerValue).toEqual('nice');
  });
});
