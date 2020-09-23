import '@testing-library/jest-dom/extend-expect';
import {useShortcut} from './use-shortcut';
import {renderHook} from '@testing-library/react-hooks';
import {Key} from 'shared/key';
import {fireEvent} from 'storybook/test-util';

test('It can register listener on keyboard events', async () => {
  const callback = jest.fn();

  const {waitForNextUpdate, rerender} = renderHook(() => useShortcut(Key.Space, callback));

  try {
    await waitForNextUpdate({timeout: 100});
  } catch (err) {
    // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
    expect(err.timeout).toBeTruthy();
  }

  rerender();

  fireEvent.keyDown(document, {key: 'Space', code: 'Space'});
  // document.dispatchEvent(new KeyboardEvent('keydown', {code: 'Space'}));

  expect(callback).toBeCalled();
});

// test('It does nothing if the key does not match', () => {
//   const callback = jest.fn();

//   renderHook(() => useShortcut(Key.Enter, callback));
//   document.dispatchEvent(new KeyboardEvent('keydown', {code: 'Space'}));

//   expect(callback).not.toBeCalled();
// });
