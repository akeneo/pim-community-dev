import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {UserFilter} from './UserFilter';

jest.mock('../../hooks/useJobExecutionUsers', () => ({
  useJobExecutionUsers: (): string[] => ['admin', 'peter'],
}));

test('it opens a dropdown when clicking on the filter', () => {
  renderWithProviders(<UserFilter userFilterValue={[]} onUserFilterChange={jest.fn()} />);

  expect(screen.queryByTitle('akeneo_job_process_tracker.user_filter.search')).not.toBeInTheDocument();

  userEvent.click(screen.getByText('akeneo_job_process_tracker.user_filter.all'));

  expect(screen.getByTitle('akeneo_job_process_tracker.user_filter.search')).toBeInTheDocument();
  userEvent.click(screen.getByTestId('backdrop'));
  expect(screen.queryByTitle('akeneo_job_process_tracker.user_filter.search')).not.toBeInTheDocument();
});

test('it can select all user', () => {
  const handleChange = jest.fn();

  renderWithProviders(<UserFilter userFilterValue={['peter']} onUserFilterChange={handleChange} />);

  userEvent.click(screen.getByText('akeneo_job_process_tracker.user_filter.label:'));
  userEvent.click(screen.getByText('akeneo_job_process_tracker.user_filter.all'));

  expect(handleChange).toHaveBeenCalledWith([]);
});

test('it can select multiple user', () => {
  const handleChange = jest.fn();

  renderWithProviders(<UserFilter userFilterValue={['peter']} onUserFilterChange={handleChange} />);

  userEvent.click(screen.getByText('akeneo_job_process_tracker.user_filter.label:'));
  userEvent.click(screen.getByText('admin'));

  expect(handleChange).toHaveBeenCalledWith(['peter', 'admin']);
});

test('it can unselect a user', () => {
  const handleChange = jest.fn();

  renderWithProviders(<UserFilter userFilterValue={['admin', 'peter']} onUserFilterChange={handleChange} />);

  userEvent.click(screen.getByText('akeneo_job_process_tracker.user_filter.label:'));
  userEvent.click(screen.getByText('admin'));

  expect(handleChange).toHaveBeenCalledWith(['peter']);
});
