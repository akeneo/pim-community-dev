import React from 'react';
import {Overlay} from './Overlay';
import {render, fireEvent, screen} from '../../../storybook/test-util';

test('it closes the overlay if backdrop is clicked', () => {
  const onClose = jest.fn();

  render(<Overlay onClose={onClose}>Content</Overlay>);

  fireEvent.click(screen.getByTestId('backdrop'));

  expect(onClose).toBeCalledTimes(1);
});
