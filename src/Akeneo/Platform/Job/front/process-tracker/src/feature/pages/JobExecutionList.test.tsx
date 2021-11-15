import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {JobExecutionList} from './JobExecutionList';
import {JobExecutionRow, JobExecutionTable, JobStatus} from '../models';

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
];

jest.mock('@akeneo-pim-community/shared/lib/components/PimView', () => ({
  PimView: () => <></>,
}));

jest.mock('../hooks/useJobExecutionTable', () => ({
  useJobExecutionTable: (page: number, size: number, type: string[], status: JobStatus[]): JobExecutionTable => {
    const filteredRows = rows.filter(
      row => (0 === type.length || type.includes(row.type)) && (0 === status.length || status.includes(row.status))
    );

    return {
      rows: filteredRows,
      matches_count: filteredRows.length,
      total_count: filteredRows.length,
    };
  },
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
  userEvent.click(screen.getByText('akeneo_job_process_tracker.status.completed'));

  expect(screen.getByText('Import job')).toBeInTheDocument();
  expect(screen.queryByText('Export job')).not.toBeInTheDocument();
});
