import React from 'react';
import {TabBar} from './TabBar';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(<TabBar>TabBar content</TabBar>);

  expect(screen.getByText('TabBar content')).toBeInTheDocument();
});

test('TabBar supports ...rest props', () => {
  render(<TabBar data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
