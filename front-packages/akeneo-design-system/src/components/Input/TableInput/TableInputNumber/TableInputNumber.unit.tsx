import React from 'react';
import {TableInputNumber} from './TableInputNumber';
import {render, screen} from '../../../../storybook/test-util';
import {TableInput} from '../TableInput';

test('TableInputNumber supports ...rest props', () => {
  const handleChange = jest.fn();

  render(
    <TableInputNumber id="myInput" value="12" onChange={handleChange} data-testid="my_value" highlighted={true} />
  );
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('it displays input in readonly mode', () => {
  render(
    <TableInput readOnly={true}>
      <tbody>
        <tr>
          <td>
            <TableInputNumber value="42" />
          </td>
        </tr>
      </tbody>
    </TableInput>
  );

  expect(screen.getByText('42')).toBeInTheDocument();
  expect(screen.getByTitle('42')).toBeInTheDocument();
});
