import React from 'react';
import {BreadcrumbTraining} from './BreadcrumbTraining';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <BreadcrumbTraining>
      <BreadcrumbTraining.Level>First</BreadcrumbTraining.Level>
      <span>yolo</span>
    </BreadcrumbTraining>
  );

  expect(screen.getByText('First')).toBeInTheDocument();
  expect(screen.queryByText('yolo')).not.toBeInTheDocument();
});

test('BreadcrumbTraining supports ...rest props', () => {
  render(<BreadcrumbTraining data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
