import React from 'react';
import {Overlay} from './Overlay';
import {render, screen, fireEvent} from '../../../storybook/test-util';

test('it closes the overlay if escape is hit', () => {
  const onClose = jest.fn();

  render(<Overlay onClose={onClose}>Content</Overlay>);

  fireEvent.keyDown(window, {key: 'Escape', code: 'Escape'});

  expect(onClose).toBeCalledTimes(1);
});

test('it closes the overlay if backdrop is clicked', () => {
  const onClose = jest.fn();

  render(<Overlay onClose={onClose}>Content</Overlay>);

  fireEvent.click(screen.getByTestId('backdrop'));

  expect(onClose).toBeCalledTimes(1);
});
