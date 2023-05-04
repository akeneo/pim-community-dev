import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import {SummaryTable} from './SummaryTable';
import {JobExecution} from '../../../models';

const jobExecution: JobExecution = {
  jobInstance: {
    code: 'csv_product_export',
    label: 'Demo CSV product export',
    type: 'other',
  },
  status: 'success',
  isRunning: false,
  failures: [
    'a job failure',
    {
      label: 'another job failure',
    },
  ],
  isStoppable: true,
  tracking: {
    error: false,
    warning: false,
    status: 'IN_PROGRESS',
    currentStep: 1,
    totalSteps: 1,
    steps: [
      {
        jobName: 'csv_product_export',
        stepName: 'export',
        status: 'IN_PROGRESS',
        isTrackable: true,
        hasWarning: false,
        hasError: false,
        duration: 14,
        processedItems: 30,
        totalItems: 135,
      },
    ],
  },
  meta: {
    logExists: true,
    archives: {
      output: {
        label: 'pim_enrich.entity.job_execution.module.download.output',
        files: {
          'export_Demo_CSV_product_export_2021-01-05_10-33-34.csv':
            'export/csv_product_export/24/output/export_Demo_CSV_product_export_2021-01-05_10-33-34.csv',
        },
      },
      archive: {
        label: 'pim_enrich.entity.job_execution.module.download.archive',
        files: {
          'export_Demo_CSV_product_export_2021-01-05_10-33-34.zip':
            'export/csv_product_export/24/archive/export_Demo_CSV_product_export_2021-01-05_10-33-34.zip',
        },
      },
    },
  },
  stepExecutions: [
    {
      job: 'product_export',
      label: 'Export file to export',
      status: 'IN_PROGRESS',
      summary: {},
      startedAt: '12',
      endedAt: '13',
      warnings: [],
      errors: [],
      failures: [],
    },
    {
      job: 'product_export',
      label: 'Clean file to export',
      status: 'FINISHED',
      summary: {},
      startedAt: '14',
      endedAt: '15',
      warnings: [],
      errors: ['a step error'],
      failures: ['a step failure'],
    },
    {
      job: 'product_export',
      label: 'Create file to export',
      status: 'FINISHED',
      summary: {},
      startedAt: '14',
      endedAt: '15',
      warnings: [
        {
          reason: 'a reason',
          item: {my: 'item'},
        },
      ],
      errors: [],
      failures: [],
    },
    {
      job: 'product_export',
      label: 'Another step',
      status: 'akeneo_job.job_status.PAUSED',
      summary: {},
      startedAt: '15',
      endedAt: '16',
      warnings: [],
      errors: [],
      failures: [],
    },
  ],
};

test('it displays a summary table', () => {
  renderWithProviders(<SummaryTable jobExecution={jobExecution} />);

  expect(screen.getByText('batch_jobs.default_steps.Export file to export')).toBeInTheDocument();
  expect(screen.getByText('IN_PROGRESS')).toBeInTheDocument();
  expect(screen.getByText('12')).toBeInTheDocument();
  expect(screen.getByText('a step error')).toBeInTheDocument();
  expect(screen.getByText('a step failure')).toBeInTheDocument();
  expect(screen.getByText('a job failure')).toBeInTheDocument();
  expect(screen.getByText('another job failure')).toBeInTheDocument();
});
