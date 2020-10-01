import {renderHook} from '@testing-library/react-hooks';
import {fireEvent} from '../storybook/test-util';
import {useShortcut} from './useShortcut';
import {Key} from '../shared';

test('It can register listener on keyboard events', () => {
  const callback = jest.fn();

  renderHook(() => useShortcut(Key.Space, callback));

  fireEvent.keyDown(document, {key: 'Space', code: 'Space'});
  expect(callback).toHaveBeenCalled();
});

test('It does not listen on other event', () => {
  const callback = jest.fn();

  renderHook(() => useShortcut(Key.Enter, callback));

  fireEvent.keyDown(document, {key: 'Space', code: 'Space'});
  expect(callback).not.toHaveBeenCalled();
});
