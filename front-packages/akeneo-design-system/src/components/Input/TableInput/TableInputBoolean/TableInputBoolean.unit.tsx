import React from 'react';
import {TableInputBoolean} from './TableInputBoolean';
import {fireEvent, render, screen} from '../../../../storybook/test-util';
import {TableInput} from '../TableInput';

test('it renders a Yes boolean input', () => {
  const handleChange = jest.fn();
  render(
    <TableInputBoolean
      value={true}
      onChange={handleChange}
      yesLabel="Yes"
      noLabel="No"
      clearLabel={'Clear'}
      openDropdownLabel={'Open'}
    />
  );

  expect(screen.getByText('Yes')).toBeInTheDocument();
});

test('it calls Callbacks on No change', () => {
  const handleChange = jest.fn();
  render(
    <TableInputBoolean
      value={true}
      onChange={handleChange}
      yesLabel="Yes"
      noLabel="No"
      clearLabel={'Clear'}
      openDropdownLabel={'Open'}
    />
  );

  fireEvent.click(screen.getByTitle('Open'));
  fireEvent.click(screen.getByText('No'));
  expect(handleChange).toHaveBeenCalledWith(false);
});

test('it calls Callbacks on Yes change', () => {
  const handleChange = jest.fn();
  render(
    <TableInputBoolean
      value={false}
      onChange={handleChange}
      yesLabel="Yes"
      noLabel="No"
      clearLabel={'Clear'}
      openDropdownLabel={'Open'}
    />
  );

  fireEvent.click(screen.getByText('No'));
  fireEvent.click(screen.getByText('Yes'));
  expect(handleChange).toHaveBeenCalledWith(true);
});

test('it does not open options on readonly mode', () => {
  const handleChange = jest.fn();
  render(
    <TableInput readOnly={true}>
      <tbody>
        <tr>
          <td>
            <TableInputBoolean
              value={true}
              onChange={handleChange}
              yesLabel="Yes"
              noLabel="No"
              clearLabel={'Clear'}
              openDropdownLabel={'Open'}
            />
          </td>
        </tr>
      </tbody>
    </TableInput>
  );

  fireEvent.click(screen.getByText('Yes'));
  expect(screen.queryByText('No')).not.toBeInTheDocument();
});

test('it clears the field', () => {
  const handleChange = jest.fn();
  render(
    <TableInputBoolean
      value={true}
      onChange={handleChange}
      clearLabel="Clear"
      yesLabel="Yes"
      noLabel="No"
      openDropdownLabel={'Open'}
    />
  );

  const clearButton = screen.getByTitle('Clear');
  fireEvent.click(clearButton);

  expect(handleChange).toHaveBeenCalledWith(null);
});

test('TableInputBoolean supports ...rest props', () => {
  const handleChange = jest.fn();

  render(
    <TableInputBoolean
      id="myInput"
      value={true}
      onChange={handleChange}
      data-testid="my_value"
      yesLabel="Yes"
      noLabel="No"
      highlighted={true}
      clearLabel={'Clear'}
      openDropdownLabel={'Open'}
    />
  );
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
