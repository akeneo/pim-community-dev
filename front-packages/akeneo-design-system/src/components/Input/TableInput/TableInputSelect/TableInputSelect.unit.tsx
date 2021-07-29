import React from 'react';
import {TableInputSelect} from './TableInputSelect';
import {fireEvent, render, screen} from '../../../../storybook/test-util';
import {TableInput} from '../TableInput';

test('it renders a Select input', () => {
  const handleClear = jest.fn();
  render(
    <TableInputSelect
      value={'Option1'}
      openDropdownLabel={'Open'}
      clearLabel={'Remove'}
      onClear={handleClear}
      searchPlaceholder={'Search'}
      searchTitle={'Search'}
    />
  );

  expect(screen.getByText('Option1')).toBeInTheDocument();
  fireEvent.click(screen.getByText('Option1'));
  expect(screen.getByTitle('Search')).toBeInTheDocument();
});

test('it clears the field', () => {
  const handleClear = jest.fn();
  render(
    <TableInputSelect
      value={'Option1'}
      openDropdownLabel={'Open'}
      clearLabel={'Clear'}
      onClear={handleClear}
      searchPlaceholder={'Search'}
      searchTitle={'Search'}
    />
  );

  const clearButton = screen.getByTitle('Clear');
  fireEvent.click(clearButton);

  expect(handleClear).toHaveBeenCalled();
});

test('it callbacks search', () => {
  const handleClear = jest.fn();
  const handleSearch = jest.fn();
  render(
    <TableInputSelect
      value={'Option1'}
      openDropdownLabel={'Open'}
      clearLabel={'Clear'}
      onClear={handleClear}
      searchPlaceholder={'Search'}
      searchTitle={'Search'}
      onSearchChange={handleSearch}
    />
  );

  fireEvent.click(screen.getByTitle('Open'));
  fireEvent.change(screen.getByTitle('Search'), {target: {value: 'The search'}});
  expect(handleSearch).toBeCalledWith('The search');
});

test('it does not open dropdown on readonly', () => {
  render(
    <TableInput readOnly={true}>
      <tbody>
        <tr>
          <td>
            <TableInputSelect
              value={'Option1'}
              openDropdownLabel={'Open'}
              clearLabel={'Clear'}
              onClear={jest.fn()}
              searchPlaceholder={'Search'}
              searchTitle={'Search'}
              onSearchChange={jest.fn()}
            />
          </td>
        </tr>
      </tbody>
    </TableInput>
  );

  fireEvent.click(screen.getByTitle('Option1'));
  expect(screen.queryByTitle('Search')).not.toBeInTheDocument();
});

test('TableInputSelect supports ...rest props', () => {
  const handleClear = jest.fn();

  render(
    <TableInputSelect
      value={'Option1'}
      openDropdownLabel={'Open'}
      clearLabel={'Remove'}
      onClear={handleClear}
      searchPlaceholder={'Search'}
      searchTitle={'Search'}
      data-testid="my_value"
      highlighted={true}
    />
  );
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
