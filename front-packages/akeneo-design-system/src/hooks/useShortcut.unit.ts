import {renderHook} from '@testing-library/react-hooks';
import {fireEvent} from '../storybook/test-util';
import {useShortcut} from './useShortcut';
import {Key} from '../shared';

test('It can register listener on keyboard events', () => {
  const callback = jest.fn();

  renderHook(() => useShortcut(Key.Space, callback));

  fireEvent.keyDown(document, {key: ' ', code: 'Space'});
  expect(callback).toHaveBeenCalled();
});

test('It listens only on the given Key events', () => {
  const callback = jest.fn();

  renderHook(() => useShortcut(Key.Enter, callback));

  fireEvent.keyDown(document, {key: ' ', code: 'Space'});
  expect(callback).not.toHaveBeenCalled();
});

test('It can listen on a provided ref', () => {
  const callback = jest.fn();

  const ref = {
    current: document.createElement('div'),
  };

  renderHook(() => useShortcut(Key.Space, callback, ref));

  fireEvent.keyDown(ref.current, {key: ' ', code: 'Space'});
  expect(callback).toHaveBeenCalled();
});

test('It does not listen on document events when a ref is provided', () => {
  const callback = jest.fn();

  const ref = {
    current: document.createElement('div'),
  };

  renderHook(() => useShortcut(Key.Space, callback, ref));

  fireEvent.keyDown(document, {key: ' ', code: 'Space'});
  expect(callback).not.toHaveBeenCalled();
});
