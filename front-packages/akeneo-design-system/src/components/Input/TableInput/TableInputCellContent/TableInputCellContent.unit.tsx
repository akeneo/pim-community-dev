import React from 'react';
import {render, screen} from '../../../../storybook/test-util';
import {TableInputCellContent} from './TableInputCellContent';

test('it renders its children properly', () => {
  render(<TableInputCellContent>Cell content</TableInputCellContent>);

  expect(screen.getByText('Cell content')).toBeInTheDocument();
});

test('TableInputCell supports ...rest props', () => {
  render(<TableInputCellContent rowTitle={true} highlighted={true} inError={true} data-testid="my_value" />);

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
