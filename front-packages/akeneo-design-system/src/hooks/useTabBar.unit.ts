import {renderHook, act} from '@testing-library/react-hooks';
import {useTabBar} from './useTabBar';

test('It handle tab bar state', () => {
  const {result} = renderHook(() => useTabBar('content'));

  const [isCurrent, switchTo, currentTab] = result.current;

  expect(isCurrent('content')).toBe(true);
  expect(isCurrent('edit')).toBe(false);
  expect(isCurrent('confirm')).toBe(false);
  expect(currentTab).toEqual('content');

  void act(() => {
    switchTo('history');
  });

  const [isCurrentHistory, _, currentTabHistory] = result.current;

  expect(isCurrentHistory('history')).toBe(true);
  expect(isCurrentHistory('edit')).toBe(false);
  expect(isCurrentHistory('content')).toBe(false);
  expect(currentTabHistory).toEqual('history');
});
