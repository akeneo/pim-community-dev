import React from 'react';
import {Overlay} from './Overlay';
import {render, screen, fireEvent} from '../../../storybook/test-util';
import 'jest-styled-components';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from '../../../theme/pim/index';

test('it closes the overlay if escape is hit', () => {
  const onClose = jest.fn();

  render(<Overlay onClose={onClose}>Content</Overlay>);

  fireEvent.keyDown(window, {key: 'Escape', code: 'Escape'});

  expect(onClose).toBeCalledTimes(1);
});

test('it guesses the position best suited to display the Overlay', () => {
  const onClose = jest.fn();
  render(
    <ThemeProvider theme={pimTheme}>
      <Overlay onClose={onClose} position="up">
        Content
      </Overlay>
    </ThemeProvider>
  );
  expect(screen.getByText('Content')).toHaveStyleRule('bottom', '-1px');
  expect(screen.getByText('Content')).toHaveStyleRule('left', '-1px');
});

test('it guesses the position best suited to display the Overlay on bottom right', () => {
  const onClose = jest.fn();
  render(
    <ThemeProvider theme={pimTheme}>
      <Overlay onClose={onClose}>Content</Overlay>
    </ThemeProvider>
  );
  expect(screen.getByText('Content')).toHaveStyleRule('top', '-1px');
  expect(screen.getByText('Content')).toHaveStyleRule('left', '-1px');
});
