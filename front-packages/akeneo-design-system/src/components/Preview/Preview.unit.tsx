import React from 'react';
import {render, screen} from '../../storybook/test-util';
import {Preview} from './Preview';

test('it renders its title & its children properly', () => {
  render(
    <Preview title="Nice preview">
      <Preview.Highlight>Name</Preview.Highlight>
      Preview content
    </Preview>
  );

  expect(screen.getByText('Nice preview')).toBeInTheDocument();
  expect(screen.getByText('Name')).toBeInTheDocument();
  expect(screen.getByText('Preview content')).toBeInTheDocument();
});

test('Preview supports ...rest props', () => {
  render(<Preview title="Hello" data-testid="my_value" />);

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
