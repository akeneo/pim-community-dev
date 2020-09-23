import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {useShortcut} from './useShortcut';
import {Key} from 'shared/key';
import {fireEvent, render} from 'storybook/test-util';

const callback = jest.fn();
const Dummy = () => {
  useShortcut(Key.Enter, callback);
  return <div />;
};

test('It can register listener on keyboard events', () => {
  const {container} = render(<Dummy />);
  expect(callback).not.toBeCalled();
  fireEvent.keyDown(container, {key: 'Enter', code: 'Enter'});
  expect(callback).toBeCalled();
});

test('It does not listen on other events', () => {
  const {container} = render(<Dummy />);
  expect(callback).not.toBeCalled();
  fireEvent.keyDown(container, {key: 'Space', code: 'Space'});
  expect(callback).not.toBeCalled();
});
