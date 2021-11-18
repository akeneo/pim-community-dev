import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import {JobExecutionTable} from './JobExecutionTable';
import {JobExecutionRow} from 'feature/models/JobExecutionTable';
import {JobExecutionFilterSort} from 'feature/models';
import userEvent from '@testing-library/user-event';

const rows: JobExecutionRow[] = [
  {
    job_execution_id: 1,
    started_at: '2020-01-01T00:00:00+00:00',
    tracking: {
      total_step: 2,
      current_step: 1,
    },
    error_count: 2,
    type: 'export',
    username: 'admin',
    warning_count: 4,
    job_name: 'An export',
    status: 'STARTED',
  },
  {
    job_execution_id: 2,
    started_at: '2020-01-02T00:00:00+00:00',
    tracking: {
      total_step: 3,
      current_step: 2,
    },
    error_count: 2,
    type: 'import',
    username: 'julia',
    warning_count: 0,
    job_name: 'An import',
    status: 'COMPLETED',
  },
  {
    job_execution_id: 3,
    started_at: '2020-01-03T00:00:00+00:00',
    tracking: {
      total_step: 1,
      current_step: 1,
    },
    error_count: 2,
    type: 'quick_export',
    username: 'peter',
    warning_count: 1,
    job_name: 'A quick export',
    status: 'STARTING',
  },
];

const sort: JobExecutionFilterSort = {
  column: 'started_at',
  direction: 'ASC',
};

jest.mock('@akeneo-pim-community/shared/lib/hooks/useSecurity', () => ({
  useSecurity: () => ({
    isGranted: (acl: string) => ['pim_importexport_export_execution_show'].includes(acl),
  }),
}));

const mockRedirect = jest.fn();
jest.mock('react-router-dom', () => ({
  useHistory: () => ({
    push: mockRedirect,
  }),
}));

beforeEach(() => {
  mockRedirect.mockClear();
});

test('it renders a Job execution Table', () => {
  renderWithProviders(<JobExecutionTable jobExecutionRows={rows} onSortChange={jest.fn()} currentSort={sort} />);

  expect(screen.getByText('An export')).toBeInTheDocument();
  expect(screen.getByText('pim_import_export.widget.last_operations.job_type.export')).toBeInTheDocument();
  expect(screen.getByText('admin')).toBeInTheDocument();
  expect(screen.getByText('pim_import_export.job_status.STARTED 1/2')).toBeInTheDocument();
  expect(screen.getByText('4')).toBeInTheDocument();

  // One header and 3 row
  expect(screen.getAllByRole('row')).toHaveLength(4);
});

test('it can sort a Job execution Table', () => {
  const handleSortChange = jest.fn();

  renderWithProviders(<JobExecutionTable jobExecutionRows={rows} onSortChange={handleSortChange} currentSort={sort} />);

  userEvent.click(screen.getByText('akeneo_job_process_tracker.job_execution_list.table.headers.job_name'));

  expect(handleSortChange).toBeCalledWith({column: 'job_name', direction: 'ASC'});
});

test('it redirects to a job execution details on row click when user can show detail execution', () => {
  renderWithProviders(<JobExecutionTable jobExecutionRows={rows} onSortChange={jest.fn()} currentSort={sort} />);

  expect(mockRedirect).not.toHaveBeenCalled();
  userEvent.click(screen.getByText('An export'));
  expect(mockRedirect).toHaveBeenCalledWith('/show/1');
});

test('it redirects to a job execution details on row click when there is no ACL on the job type', () => {
  renderWithProviders(<JobExecutionTable jobExecutionRows={rows} onSortChange={jest.fn()} currentSort={sort} />);

  expect(mockRedirect).not.toHaveBeenCalled();
  userEvent.click(screen.getByText('A quick export'));
  expect(mockRedirect).toHaveBeenCalledWith('/show/3');
});

test('it does nothing on row click when user cannot show detail execution', () => {
  renderWithProviders(<JobExecutionTable jobExecutionRows={rows} onSortChange={jest.fn()} currentSort={sort} />);

  expect(mockRedirect).not.toHaveBeenCalled();
  userEvent.click(screen.getByText('An import'));
  expect(mockRedirect).not.toHaveBeenCalled();
});

test('it redirects to a job execution details on row cmd click', () => {
  const handleSortChange = jest.fn();
  const redirectMock = jest.fn();
  jest.spyOn(window, 'open').mockImplementation(url => redirectMock(url));

  renderWithProviders(<JobExecutionTable jobExecutionRows={rows} onSortChange={handleSortChange} currentSort={sort} />);
  expect(redirectMock).not.toHaveBeenCalled();
  userEvent.click(screen.getByText('An export'), {metaKey: true});
  expect(redirectMock).toHaveBeenCalledWith('/show/1');
});
