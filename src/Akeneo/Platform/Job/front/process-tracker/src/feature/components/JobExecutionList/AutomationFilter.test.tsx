import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {AutomationFilter} from './AutomationFilter';

test('it opens a dropdown when clicking on the filter', () => {
  renderWithProviders(<AutomationFilter automationFilterValue={null} onAutomationFilterChange={jest.fn()} />);

  expect(screen.queryByText('akeneo_job_process_tracker.automation_filter.label')).not.toBeInTheDocument();

  userEvent.click(screen.getByText('akeneo_job_process_tracker.automation_filter.all'));

  expect(screen.getByText('akeneo_job_process_tracker.automation_filter.label')).toBeInTheDocument();
});

test('it can select all automation', () => {
  const handleChange = jest.fn();

  renderWithProviders(<AutomationFilter automationFilterValue={true} onAutomationFilterChange={handleChange} />);

  userEvent.click(screen.getByText('akeneo_job_process_tracker.automation_filter.yes'));
  userEvent.click(screen.getByText('akeneo_job_process_tracker.automation_filter.all'));

  expect(handleChange).toHaveBeenCalledWith(null);
  expect(screen.getByText('akeneo_job_process_tracker.automation_filter.all')).toBeInTheDocument();
});

test('it can select yes in automation filter', () => {
  const handleChange = jest.fn();

  renderWithProviders(<AutomationFilter automationFilterValue={null} onAutomationFilterChange={handleChange} />);

  userEvent.click(screen.getByText('akeneo_job_process_tracker.automation_filter.all'));
  userEvent.click(screen.getByText('akeneo_job_process_tracker.automation_filter.yes'));

  expect(handleChange).toHaveBeenCalledWith(true);
  expect(screen.getByText('akeneo_job_process_tracker.automation_filter.yes')).toBeInTheDocument();
});

test('it can select no in automation filter', () => {
  const handleChange = jest.fn();

  renderWithProviders(<AutomationFilter automationFilterValue={null} onAutomationFilterChange={handleChange} />);

  userEvent.click(screen.getByText('akeneo_job_process_tracker.automation_filter.all'));
  userEvent.click(screen.getByText('akeneo_job_process_tracker.automation_filter.no'));

  expect(handleChange).toHaveBeenCalledWith(false);
  expect(screen.getByText('akeneo_job_process_tracker.automation_filter.no')).toBeInTheDocument();
});
