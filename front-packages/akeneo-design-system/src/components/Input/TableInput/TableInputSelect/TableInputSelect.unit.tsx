import React from 'react';
import {TableInputSelect} from './TableInputSelect';
import {fireEvent, render, screen} from '../../../../storybook/test-util';

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
});

test('it empty the field', () => {
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
