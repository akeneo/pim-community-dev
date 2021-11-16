import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {JobExecutionList} from './JobExecutionList';
import {JobExecutionFilter, JobExecutionRow, JobExecutionTable} from '../models';

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
    job_name: 'Export job',
    status: 'STARTED',
  },
  {
    job_execution_id: 2,
    started_at: '2020-01-01T00:00:00+00:00',
    tracking: {
      total_step: 2,
      current_step: 1,
    },
    error_count: 1,
    type: 'import',
    username: 'admin',
    warning_count: 8,
    job_name: 'Import job',
    status: 'COMPLETED',
  },
  {
    job_execution_id: 3,
    started_at: '2020-01-01T00:00:00+00:00',
    tracking: {
      total_step: 2,
      current_step: 1,
    },
    error_count: 1,
    type: 'import',
    username: 'admin',
    warning_count: 8,
    job_name: 'Another import job',
    status: 'STOPPING',
  },
  {
    job_execution_id: 4,
    started_at: '2020-01-01T00:00:00+00:00',
    tracking: {
      total_step: 2,
      current_step: 1,
    },
    error_count: 2,
    type: 'mass-edit',
    username: 'admin',
    warning_count: 5,
    job_name: 'Mass edit',
    status: 'ABANDONED',
  },
];

jest.mock('@akeneo-pim-community/shared/lib/components/PimView', () => ({
  PimView: () => <></>,
}));

jest.mock('../hooks/useJobExecutionTable', () => ({
  useJobExecutionTable: ({page, size, sort, type, status}: JobExecutionFilter): JobExecutionTable => {
    const filteredRows = rows
      .filter(
        row => (0 === type.length || type.includes(row.type)) && (0 === status.length || status.includes(row.status))
      )
      .sort((a, b) => {
        return 'ASC' === sort.direction
          ? a[sort.column].localeCompare(b[sort.column])
          : b[sort.column].localeCompare(a[sort.column]);
      })
      .slice((page - 1) * size, (page - 1) * size + size);

    return {
      rows: filteredRows,
      matches_count: 4,
      total_count: 4,
    };
  },
}));

jest.mock('../hooks/useJobExecutionTypes', () => ({
  useJobExecutionTypes: (): string[] => ['import', 'export', 'mass_edit'],
}));

jest.mock('../models/JobExecutionFilter', () => ({
  getDefaultJobExecutionFilter: () => ({
    page: 1,
    size: 3,
    sort: {column: 'job_name', direction: 'ASC'},
    status: [],
    type: [],
  }),
}));

test('it renders a breadcrumb', () => {
  renderWithProviders(<JobExecutionList />);

  expect(screen.getByText('pim_menu.tab.activity')).toBeInTheDocument();
  expect(screen.getByText('pim_menu.item.job_tracker')).toBeInTheDocument();
});

test('it renders the matches job execution count in page header', () => {
  renderWithProviders(<JobExecutionList />);

  expect(screen.getByText('pim_enrich.entity.job_execution.page_title.index')).toBeInTheDocument();
});

test('it can filter on the job status', () => {
  renderWithProviders(<JobExecutionList />);

  userEvent.click(screen.getByLabelText('akeneo_job_process_tracker.status.label:'));
  userEvent.click(screen.getByText('akeneo_job_process_tracker.status.started'));

  expect(screen.getByText('Export job')).toBeInTheDocument();
  expect(screen.queryByText('Import job')).not.toBeInTheDocument();
});

test('it can filter on the job type', () => {
  renderWithProviders(<JobExecutionList />);

  userEvent.click(screen.getByLabelText('akeneo_job_process_tracker.type.label:'));
  userEvent.click(screen.getByText('akeneo_job_process_tracker.type.import'));

  expect(screen.getByText('Import job')).toBeInTheDocument();
  expect(screen.queryByText('Export job')).not.toBeInTheDocument();
});

test('it can sort on the job name', () => {
  renderWithProviders(<JobExecutionList />);

  expect(screen.getByText('Another import job')).toBeInTheDocument();

  userEvent.click(screen.getByText('akeneo_job_process_tracker.job_execution_list.table.headers.job_name'));

  expect(screen.queryByText('Another import job')).not.toBeInTheDocument();
});

test('it can change page', () => {
  renderWithProviders(<JobExecutionList />);

  userEvent.click(screen.getByTitle('No. 2'));

  expect(screen.queryByText('Import job')).not.toBeInTheDocument();
  expect(screen.queryByText('Export job')).not.toBeInTheDocument();
  expect(screen.getByText('Mass edit')).toBeInTheDocument();
});
