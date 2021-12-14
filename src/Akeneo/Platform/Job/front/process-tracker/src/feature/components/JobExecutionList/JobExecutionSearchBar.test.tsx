import React from 'react';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {JobExecutionSearchBar} from './JobExecutionSearchBar';
import {getDefaultJobExecutionFilter} from '../../models';
import {renderWithProviders} from '@akeneo-pim-community/shared';

test('It displays a search input with an initialized value', () => {
  renderWithProviders(
    <JobExecutionSearchBar
      jobExecutionFilter={{...getDefaultJobExecutionFilter(), search: 'Search value'}}
      onTypeFilterChange={jest.fn()}
      onSearchChange={jest.fn()}
      onStatusFilterChange={jest.fn()}
      onUserFilterChange={jest.fn()}
    />
  );

  expect(screen.getByDisplayValue('Search value')).toBeInTheDocument();
});

test('It triggers the onSearchChange when the search field changes', () => {
  jest.useFakeTimers();
  const handleSearchChange = jest.fn();

  renderWithProviders(
    <JobExecutionSearchBar
      jobExecutionFilter={{...getDefaultJobExecutionFilter(), search: ''}}
      onTypeFilterChange={jest.fn()}
      onSearchChange={handleSearchChange}
      onStatusFilterChange={jest.fn()}
      onUserFilterChange={jest.fn()}
    />
  );

  const searchInput = screen.getByPlaceholderText('akeneo_job_process_tracker.job_execution_list.search_placeholder');
  userEvent.type(searchInput, 'New value');

  act(() => {
    jest.runAllTimers();
  });

  expect(handleSearchChange).toHaveBeenCalledWith('New value');
});

test('It triggers the onSearchChange when the search field is emptied', () => {
  jest.useFakeTimers();
  const handleSearchChange = jest.fn();

  renderWithProviders(
    <JobExecutionSearchBar
      jobExecutionFilter={{...getDefaultJobExecutionFilter(), search: ''}}
      onTypeFilterChange={jest.fn()}
      onSearchChange={handleSearchChange}
      onStatusFilterChange={jest.fn()}
      onUserFilterChange={jest.fn()}
    />
  );

  const searchInput = screen.getByPlaceholderText('akeneo_job_process_tracker.job_execution_list.search_placeholder');
  userEvent.type(searchInput, 'New value');
  act(() => {
    jest.runAllTimers();
  });

  expect(handleSearchChange).toHaveBeenCalledWith('New value');
  userEvent.clear(searchInput);
  act(() => {
    jest.runAllTimers();
  });

  expect(handleSearchChange).toBeCalledWith('');
});
