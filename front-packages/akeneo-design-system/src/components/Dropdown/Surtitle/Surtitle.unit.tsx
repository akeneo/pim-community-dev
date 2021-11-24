import React from 'react';
import {Surtitle} from './Surtitle';
import {render, screen} from '../../../storybook/test-util';

test('it renders its children properly', () => {
  render(<Surtitle label="Item title">Item child</Surtitle>);

  expect(screen.getByText('Item title')).toBeInTheDocument();
  expect(screen.getByText('Item child')).toBeInTheDocument();
});

test('Surtitle supports ...rest props', () => {
  render(<Surtitle label="Icon button" data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
