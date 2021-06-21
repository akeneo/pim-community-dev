import React from 'react';
import {TableInput} from './TableInput';
import {fireEvent, render, screen} from '../../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <TableInput>
      <tbody>
        <tr>
          <td>Table content</td>
        </tr>
      </tbody>
    </TableInput>
  );

  expect(screen.getByText('Table content')).toBeInTheDocument();
});

test('it scrolls', () => {
  render(
    <TableInput data-testid="tableContainer">
      <tbody>
        <tr>
          <td>Table content</td>
        </tr>
      </tbody>
    </TableInput>
  );

  fireEvent.scroll(screen.getByTestId('tableContainer'), 100);
});

test('TableInput supports ...rest props', () => {
  render(<TableInput data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
