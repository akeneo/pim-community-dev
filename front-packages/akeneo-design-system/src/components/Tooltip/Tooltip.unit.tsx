import React from 'react';
import {fireEvent, render, screen} from '../../storybook/test-util';
import {Tooltip} from './Tooltip';

test('it renders its children properly', () => {
  render(<Tooltip data-testid="my_value">Tooltip content</Tooltip>);
  fireEvent.mouseOver(screen.getByTestId('my_value'));
  expect(screen.getByText('Tooltip content')).toBeInTheDocument();
});

test('it triggers tooltip mouse events', () => {
  render(<Tooltip data-testid="my_value">Tooltip content</Tooltip>);
  fireEvent.mouseOver(screen.getByTestId('my_value'));
  expect(screen.getByText('Tooltip content')).toBeInTheDocument();

  fireEvent.mouseLeave(screen.getByTestId('my_value'));
  expect(screen.queryByText('Tooltip content')).not.toBeInTheDocument();
});

test('it renders the tooltip with a bottom direction', () => {
  render(
    <Tooltip data-testid="my_value" direction={'bottom'}>
      Tooltip content
    </Tooltip>
  );
  fireEvent.mouseOver(screen.getByTestId('my_value'));
  expect(screen.getByText('Tooltip content')).toBeInTheDocument();
});

test('it renders the tooltip with a left direction', () => {
  render(
    <Tooltip data-testid="my_value" direction={'left'}>
      Tooltip content
    </Tooltip>
  );
  fireEvent.mouseOver(screen.getByTestId('my_value'));
  expect(screen.getByText('Tooltip content')).toBeInTheDocument();
});

test('it renders the tooltip with a right direction', () => {
  render(
    <Tooltip data-testid="my_value" direction={'right'}>
      Tooltip content
    </Tooltip>
  );
  fireEvent.mouseOver(screen.getByTestId('my_value'));
  expect(screen.getByText('Tooltip content')).toBeInTheDocument();
});

test('Tooltip supports ...rest props', () => {
  render(<Tooltip data-testid="my_value">Tooltip content</Tooltip>);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
