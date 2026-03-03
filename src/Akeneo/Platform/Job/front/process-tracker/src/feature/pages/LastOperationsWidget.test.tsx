import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import {LastOperationsWidget} from './LastOperationsWidget';
import {JobExecutionRow} from '../models';

let mockedRows: JobExecutionRow[] = [
  {
    job_execution_id: 1,
    started_at: '2020-01-01T00:00:00+00:00',
    tracking: {
      total_step: 2,
      current_step: 1,
      steps: [],
    },
    has_error: true,
    automation: null,
    type: 'export',
    username: 'admin',
    warning_count: 4,
    job_name: 'Export job',
    status: 'IN_PROGRESS',
    is_stoppable: true,
  },
  {
    job_execution_id: 2,
    started_at: '2020-01-01T00:00:00+00:00',
    tracking: {
      total_step: 2,
      current_step: 1,
      steps: [],
    },
    has_error: true,
    automation: null,
    type: 'import',
    username: 'admin',
    warning_count: 8,
    job_name: 'Import job',
    status: 'COMPLETED',
    is_stoppable: true,
  },
  {
    job_execution_id: 3,
    started_at: '2020-01-01T00:00:00+00:00',
    tracking: {
      total_step: 2,
      current_step: 1,
      steps: [],
    },
    has_error: true,
    automation: null,
    type: 'import',
    username: 'admin',
    warning_count: 8,
    job_name: 'Another import job',
    status: 'STOPPING',
    is_stoppable: true,
  },
  {
    job_execution_id: 4,
    started_at: '2020-01-01T00:00:00+00:00',
    tracking: {
      total_step: 2,
      current_step: 2,
      steps: [],
    },
    has_error: true,
    automation: null,
    type: 'mass-edit',
    username: 'peter',
    warning_count: 5,
    job_name: 'Mass edit',
    status: 'COMPLETED',
    is_stoppable: false,
  },
];

jest.mock('../hooks/useJobExecutionTable', () => ({
  useJobExecutionTable: () => {
    const jobExecutionTable = {
      rows: mockedRows,
      matches_count: mockedRows.length,
    };

    return [jobExecutionTable];
  },
}));

test('it renders the last operations', () => {
  renderWithProviders(<LastOperationsWidget />);

  // Section title
  expect(screen.getByText('akeneo_job_process_tracker.last_operations.title')).toBeInTheDocument();
  expect(screen.getByText('akeneo_job_process_tracker.last_operations.view_all')).toBeInTheDocument();

  // 1 header row + 4 operation rows
  expect(screen.getAllByRole('row')).toHaveLength(1 + 4);

  // All expected columns
  ['started_at', 'type', 'job_name', 'username', 'status', 'warning_count'].forEach(column => {
    expect(
      screen.getByText(`akeneo_job_process_tracker.job_execution_list.table.headers.${column}`)
    ).toBeInTheDocument();
  });
  expect(screen.getAllByText('akeneo_job.job_status.COMPLETED')).toHaveLength(2);
});

test('it renders a placeholder when there is no operation', () => {
  mockedRows = [];

  renderWithProviders(<LastOperationsWidget />);

  expect(screen.getByText('akeneo_job_process_tracker.last_operations.no_result')).toBeInTheDocument();
  expect(screen.queryByRole('table')).not.toBeInTheDocument();
});
