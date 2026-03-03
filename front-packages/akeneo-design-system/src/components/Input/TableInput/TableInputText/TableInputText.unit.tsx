import React from 'react';
import {TableInputText} from './TableInputText';
import {render, screen} from '../../../../storybook/test-util';
import {TableInput} from '../TableInput';

test('TableInputText supports ...rest props', () => {
  const handleChange = jest.fn();

  render(
    <TableInputText id="myInput" value="Nice" onChange={handleChange} data-testid="my_value" highlighted={true} />
  );
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('it displays input in readonly mode', () => {
  render(
    <TableInput readOnly={true}>
      <tbody>
        <tr>
          <td>
            <TableInputText value="Noice" />
          </td>
        </tr>
      </tbody>
    </TableInput>
  );

  expect(screen.getByText('Noice')).toBeInTheDocument();
  expect(screen.getByTitle('Noice')).toBeInTheDocument();
});
