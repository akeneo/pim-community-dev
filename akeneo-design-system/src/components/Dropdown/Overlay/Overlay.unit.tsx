import React from 'react';
import {Overlay} from './Overlay';
import {render, screen, fireEvent} from '../../../storybook/test-util';
import 'jest-styled-components';

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

test('it guesses the position best suited to display the Overlay', () => {
  const onClose = jest.fn();
  render(
    <Overlay onClose={onClose} verticalPosition="up">
      Content
    </Overlay>
  );
  expect(screen.getByText('Content')).toHaveStyleRule('bottom', '-1px');
  expect(screen.getByText('Content')).toHaveStyleRule('left', '-1px');
});

test('it guesses the position best suited to display the Overlay on bottom right', () => {
  const onClose = jest.fn();
  render(<Overlay onClose={onClose}>Content</Overlay>);
  expect(screen.getByText('Content')).toHaveStyleRule('top', '-1px');
  expect(screen.getByText('Content')).toHaveStyleRule('left', '-1px');
});
