import React from 'react';
import {BreadcrumbFox} from './BreadcrumbFox';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <BreadcrumbFox>
      <BreadcrumbFox.Item>First</BreadcrumbFox.Item>
      <BreadcrumbFox.Item>Second</BreadcrumbFox.Item>
      Last
    </BreadcrumbFox>
  );

  expect(screen.getByText('First')).toBeInTheDocument();
  expect(screen.getByText('Second')).toBeInTheDocument();
  expect(screen.queryByText('Last')).not.toBeInTheDocument();
});

test('BreadcrumbFox supports ...rest props', () => {
  render(<BreadcrumbFox data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
