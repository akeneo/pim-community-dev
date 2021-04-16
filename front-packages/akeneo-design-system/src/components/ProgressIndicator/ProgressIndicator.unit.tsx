import React from 'react';
import {ProgressIndicator} from './ProgressIndicator';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <ProgressIndicator>
      <ProgressIndicator.Step>First step</ProgressIndicator.Step>
      <ProgressIndicator.Step current>Second step</ProgressIndicator.Step>
      <ProgressIndicator.Step>Third step</ProgressIndicator.Step>
    </ProgressIndicator>
  );

  expect(screen.getByText('First step')).toBeInTheDocument();
  expect(screen.getByText('Second step')).toBeInTheDocument();
  expect(screen.getByText('Third step')).toBeInTheDocument();
  expect(screen.getByText('Second step').parentElement).toHaveAttribute('aria-current', 'step');
});
test('it cannot render random childrens', () => {
  const mockConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    render(<ProgressIndicator>nice</ProgressIndicator>);
  }).toThrowError('ProgressIndicator only accepts `ProgressIndicator.Step` elements as children');

  mockConsole.mockRestore();
});
test('a step cannot be used outside of a component', () => {
  const mockConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    render(<ProgressIndicator.Step>nice</ProgressIndicator.Step>);
  }).toThrowError('ProgressIndicator.Step cannot be used outside a ProgressIndicator component');

  mockConsole.mockRestore();
});

test('ProgressIndicator supports ...rest props', () => {
  render(<ProgressIndicator data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
