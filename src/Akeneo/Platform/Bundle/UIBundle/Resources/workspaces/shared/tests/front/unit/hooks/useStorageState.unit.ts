'use strict';

import '@testing-library/jest-dom/extend-expect';
import {useStorageState} from '../../../../src/hooks/useStorageState';
import {renderHook} from '@testing-library/react-hooks';

test('It can store the state in local storage', async () => {
  renderHook(() => useStorageState('default', 'storage_key'));

  const storage = JSON.parse(localStorage.getItem('storage_key') || '');

  expect(storage).toEqual('default');

  const {result} = renderHook(() => useStorageState('another one', 'storage_key'));
  const [value] = result.current;

  expect(value).toEqual('default');
});
