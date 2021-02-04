import React from 'react';
import {ProgressIndicator} from './ProgressIndicator';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <ProgressIndicator>
      <ProgressIndicator.Step completed>First step</ProgressIndicator.Step>
      <ProgressIndicator.Step>Second step</ProgressIndicator.Step>
    </ProgressIndicator>
  );

  expect(screen.getByText('First step')).toBeInTheDocument();
  expect(screen.getByText('Second step')).toBeInTheDocument();
  expect(screen.getByText('First step').parentElement).toHaveAttribute('aria-label', 'Breadcrumb');
});

test('ProgressIndicator supports ...rest props', () => {
  render(<ProgressIndicator data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
