import React from 'react';
import styled from 'styled-components';
import {Overlay} from './Overlay';
import {render, screen, fireEvent} from '../../../storybook/test-util';

test('it closes the overlay if escape is hit', () => {
  const onClose = jest.fn();

  render(<Overlay onClose={onClose} fullWidth={true}>Content</Overlay>);

  fireEvent.keyDown(window, {key: 'Escape', code: 'Escape'});

  expect(onClose).toBeCalledTimes(1);
});

test('it closes the overlay if backdrop is clicked', () => {
  const onClose = jest.fn();

  render(<Overlay onClose={onClose}>Content</Overlay>);

  fireEvent.click(screen.getByTestId('backdrop'));

  expect(onClose).toBeCalledTimes(1);
});

test('it takes the parent full with if fullWidth props is true', () => {
  const OverlayParent = styled.div`
    width: 100px;
  `;

  render(
    <OverlayParent>
      <Overlay onClose={jest.fn()} fullWidth={true}>Content</Overlay>
    </OverlayParent>
  );

  expect(screen.getByText('Content')).toBeInTheDocument();
});
