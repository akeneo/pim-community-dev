import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import {JobExecutionTable} from './JobExecutionTable';
import {JobExecutionRow} from 'feature/models/JobExecutionTable';

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
    job_name: 'Nice name',
    status: 'STARTED',
  },
];

test('it renders a Job execution Table', () => {
  renderWithProviders(<JobExecutionTable jobExecutionRows={rows} />);

  expect(screen.getByText('Nice name')).toBeInTheDocument();
  expect(screen.getByText('pim_import_export.widget.last_operations.job_type.export')).toBeInTheDocument();
  expect(screen.getByText('admin')).toBeInTheDocument();
  expect(screen.getByText('pim_import_export.job_status.STARTED 1/2')).toBeInTheDocument();

  // One header and one row
  expect(screen.getAllByRole('row')).toHaveLength(2);
});
