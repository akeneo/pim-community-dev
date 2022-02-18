import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {act, screen, within} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {JobExecutionFilter, JobExecutionRow} from '../models';
import {JobInstanceDetail} from './JobInstanceDetail';

const rows: JobExecutionRow[] = [
  {
    job_execution_id: 1,
    started_at: '2020-01-01T00:00:00+00:00',
    tracking: {
      total_step: 2,
      current_step: 1,
      steps: [],
    },
    has_error: true,
    type: 'export',
    username: 'admin',
    warning_count: 4,
    job_name: 'An product export',
    status: 'IN_PROGRESS',
    is_stoppable: true,
  },
  {
    job_execution_id: 2,
    started_at: '2020-01-02T00:00:00+00:00',
    tracking: {
      total_step: 2,
      current_step: 1,
      steps: [],
    },
    has_error: true,
    type: 'export',
    username: 'admin',
    warning_count: 8,
    job_name: 'An product export',
    status: 'COMPLETED',
    is_stoppable: true,
  },
  {
    job_execution_id: 3,
    started_at: '2020-01-03T00:00:00+00:00',
    tracking: {
      total_step: 2,
      current_step: 1,
      steps: [],
    },
    has_error: true,
    type: 'export',
    username: 'admin',
    warning_count: 0,
    job_name: 'An product export',
    status: 'STOPPING',
    is_stoppable: true,
  },
  {
    job_execution_id: 4,
    started_at: '2020-01-04T00:00:00+00:00',
    tracking: {
      total_step: 2,
      current_step: 1,
      steps: [],
    },
    has_error: true,
    type: 'export',
    username: 'admin',
    warning_count: 5,
    job_name: 'An product export',
    status: 'ABANDONED',
    is_stoppable: false,
  },
];

const mockRefresh = jest.fn();
jest.mock('../hooks/useJobExecutionTable', () => ({
  useJobExecutionTable: ({code}: JobExecutionFilter) => {
    if (code.includes('not_executed_export')) return [{rows: [], matches_count: 0}, mockRefresh];
    if (code.includes('loading_export')) return [null, mockRefresh];

    const jobExecutionTable = {
      rows,
      matches_count: rows.length,
    };

    return [jobExecutionTable, mockRefresh];
  },
}));

beforeEach(() => mockRefresh.mockClear());

test('it display the last job instance execution', () => {
  renderWithProviders(<JobInstanceDetail code="csv_product_export" type="export" />);
  expect(screen.queryByRole('table')).toBeInTheDocument();

  expect(screen.getByText('01/01/2020, 12:00 AM')).toBeInTheDocument();
  expect(screen.getByText('akeneo_job.job_status.IN_PROGRESS 1/2')).toBeInTheDocument();
  expect(screen.getByText('4')).toBeInTheDocument();

  expect(screen.getByText('01/02/2020, 12:00 AM')).toBeInTheDocument();
  expect(screen.getByText('akeneo_job.job_status.COMPLETED')).toBeInTheDocument();
  expect(screen.getByText('8')).toBeInTheDocument();

  expect(screen.getByText('01/03/2020, 12:00 AM')).toBeInTheDocument();
  expect(screen.getByText('akeneo_job.job_status.STOPPING')).toBeInTheDocument();
  expect(screen.getByText('-')).toBeInTheDocument();

  expect(screen.getByText('01/04/2020, 12:00 AM')).toBeInTheDocument();
  expect(screen.getByText('akeneo_job.job_status.ABANDONED')).toBeInTheDocument();
  expect(screen.getByText('5')).toBeInTheDocument();
});

test('it refresh the page when user stop job', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    json: async () => {},
  }));

  renderWithProviders(<JobInstanceDetail code="csv_product_export" type="export" />);

  userEvent.click(within(screen.getAllByRole('row')[1]).getByText('pim_datagrid.action.stop.title'));
  await act(async () => {
    await userEvent.click(screen.getByText('pim_datagrid.action.stop.confirmation.ok'));
  });

  expect(mockRefresh).toBeCalledTimes(1);
});

test('it display a message when no job execution is found', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    json: async () => {},
  }));

  renderWithProviders(<JobInstanceDetail code="not_executed_export" type="export" />);

  expect(screen.getByText('pim_common.no_result')).toBeInTheDocument();
});

test('it display nothing when the job is not fetched yet', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    json: async () => {},
  }));

  renderWithProviders(<JobInstanceDetail code="loading_export" type="export" />);

  expect(screen.queryByText('pim_common.no_result')).not.toBeInTheDocument();
  expect(screen.queryByRole('table')).not.toBeInTheDocument();
});
