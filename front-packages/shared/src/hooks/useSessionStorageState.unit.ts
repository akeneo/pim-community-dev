import {renderHookWithProviders} from '../tests/utils';
import {useSessionStorageState} from './useSessionStorageState';

const mockStorage = {};
const setItemMock = jest.fn((key: string, value: string) => {
  mockStorage[key] = value;
});
const getItemMock = jest.fn((key: string) => key);

beforeEach(() => {
  Storage.prototype.setItem = setItemMock;
  Storage.prototype.getItem = getItemMock;
});

afterEach(() => {
  setItemMock.mockRestore();
  getItemMock.mockRestore();
});

test('it returns the SessionStorageState', () => {
  const {result} = renderHookWithProviders(() => useSessionStorageState('"value"', '"key"'));

  const [key] = result.current;

  expect(key).toEqual('key');
  expect(getItemMock).toHaveBeenCalledWith('"key"');
  expect(setItemMock).toHaveBeenCalledWith('"key"', '"key"');
});
