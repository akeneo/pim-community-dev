import * as React from 'react';
import {screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {JobExecutionProgress} from '../../../../Resources/public/js/job/execution/Progress';
import {StepExecutionTracking} from '../../../../Resources/public/js/job/execution/model/job-execution';

jest.mock('@akeneo-pim-community/legacy-bridge/src/hooks/useTranslate', () => ({
  useTranslate: () => {
    return jest.fn((key: string, params: any, count: number) => {
      switch (key) {
        case 'duration.days':
          return `${count} day(s)`;
        case 'duration.hours':
          return `${count} hour(s)`;
        case 'duration.minutes':
          return `${count} minute(s)`;
        case 'duration.seconds':
          return `${count} second(s)`;
        case 'batch_jobs.csv_product_export.export.label':
          return 'Product export';
        case 'pim_import_export.tracking.in_progress':
          return `${params.duration} left`;
        default:
          return key;
      }
    });
  },
}));

test('it render a progress bar with the correct label of the job step', () => {
  const steps: StepExecutionTracking[] = [
    {
      jobName: 'csv_product_export',
      stepName: 'export',
      hasError: false,
      hasWarning: false,
      isTrackable: true,
      status: 'COMPLETED',
      duration: 0,
      processedItems: 0,
      totalItems: 0,
    },
  ];

  renderWithProviders(<JobExecutionProgress steps={steps} />);

  expect(screen.getByText('Product export')).toBeInTheDocument();
});

test('it render the progress bar of one completed job step', () => {
  const steps: StepExecutionTracking[] = [
    {
      jobName: 'csv_product_export',
      stepName: 'export',
      status: 'COMPLETED',
      isTrackable: true,
      hasWarning: false,
      hasError: false,
      duration: 0,
      processedItems: 0,
      totalItems: 0,
    },
  ];

  renderWithProviders(<JobExecutionProgress steps={steps} />);

  expect(screen.getByText('pim_import_export.tracking.completed')).toBeInTheDocument();
  expect(screen.getByRole('progressbar')).toHaveAttribute('aria-valuenow', '100');
});

test('it render the progress bar of one job step in progress', () => {
  const steps: StepExecutionTracking[] = [
    {
      jobName: 'csv_product_export',
      stepName: 'export',
      status: 'STARTED',
      isTrackable: true,
      hasWarning: false,
      hasError: false,
      duration: 12,
      processedItems: 60,
      totalItems: 100,
    },
  ];

  renderWithProviders(<JobExecutionProgress steps={steps} />);

  // we are at 60% of the items, done in 12 seconds, we should expect 8 seconds left
  expect(screen.getByText('8 second(s) left')).toBeInTheDocument();
  expect(screen.getByRole('progressbar')).toHaveAttribute('aria-valuenow', '60');
});

test('it render the progress bar of one started job step without processed items', () => {
  const steps: StepExecutionTracking[] = [
    {
      jobName: 'csv_product_export',
      stepName: 'export',
      status: 'STARTED',
      isTrackable: true,
      hasWarning: false,
      hasError: false,
      duration: 12,
      processedItems: 0,
      totalItems: 100,
    },
  ];

  renderWithProviders(<JobExecutionProgress steps={steps} />);

  expect(screen.getByText('pim_import_export.tracking.estimating')).toBeInTheDocument();
  expect(screen.getByRole('progressbar')).toHaveAttribute('aria-valuenow', '0');
});

test('it render the progress bar of one pending job step', () => {
  const steps: StepExecutionTracking[] = [
    {
      jobName: 'csv_product_export',
      stepName: 'export',
      status: 'STARTING',
      isTrackable: true,
      hasWarning: false,
      hasError: false,
      duration: 0,
      processedItems: 0,
      totalItems: 0,
    },
  ];

  renderWithProviders(<JobExecutionProgress steps={steps} />);

  expect(screen.getByText('pim_import_export.tracking.not_started')).toBeInTheDocument();
  expect(screen.getByRole('progressbar')).toHaveAttribute('aria-valuenow', '0');
});

test('it render the progress bar of one untrackable and pending job step', () => {
  const steps: StepExecutionTracking[] = [
    {
      jobName: 'csv_product_export',
      stepName: 'export',
      status: 'STARTING',
      isTrackable: false,
      hasWarning: false,
      hasError: false,
      duration: 0,
      processedItems: 0,
      totalItems: 0,
    },
  ];

  renderWithProviders(<JobExecutionProgress steps={steps} />);

  expect(screen.getByText('pim_import_export.tracking.not_started')).toBeInTheDocument();
  expect(screen.getByRole('progressbar')).toHaveAttribute('aria-valuenow', '0');
});

test('it render the progress bar of one untrackable and started job step', () => {
  const steps: StepExecutionTracking[] = [
    {
      jobName: 'csv_product_export',
      stepName: 'export',
      status: 'STARTED',
      isTrackable: false,
      hasWarning: false,
      hasError: false,
      duration: 0,
      processedItems: 0,
      totalItems: 0,
    },
  ];

  renderWithProviders(<JobExecutionProgress steps={steps} />);

  expect(screen.getByText('pim_import_export.tracking.untrackable')).toBeInTheDocument();
  expect(screen.getByRole('progressbar')).not.toHaveAttribute('aria-valuenow');
});

test('it render the progress bar of one untrackable and failed job step', () => {
  const steps: StepExecutionTracking[] = [
    {
      jobName: 'csv_product_export',
      stepName: 'export',
      status: 'FAILED',
      isTrackable: false,
      hasWarning: false,
      hasError: false,
      duration: 10,
      processedItems: 0,
      totalItems: 0,
    },
  ];

  renderWithProviders(<JobExecutionProgress steps={steps} />);

  expect(screen.getByText('pim_import_export.tracking.completed')).toBeInTheDocument();
  expect(screen.getByRole('progressbar')).toHaveAttribute('aria-valuenow', '100');
});

test('it render without error the progress bar of one job step with warning', () => {
  const steps: StepExecutionTracking[] = [
    {
      jobName: 'csv_product_export',
      stepName: 'export',
      status: 'STARTED',
      isTrackable: true,
      hasWarning: true,
      hasError: false,
      duration: 12,
      processedItems: 1,
      totalItems: 100000,
    },
  ];

  renderWithProviders(<JobExecutionProgress steps={steps} />);

  expect(screen.getByRole('progressbar')).toBeInTheDocument();
});

test('it render without error the progress bar of one job step with error', () => {
  const steps: StepExecutionTracking[] = [
    {
      jobName: 'csv_product_export',
      stepName: 'export',
      status: 'STARTED',
      isTrackable: true,
      hasWarning: false,
      hasError: true,
      duration: 12,
      processedItems: 1,
      totalItems: 100000,
    },
  ];

  renderWithProviders(<JobExecutionProgress steps={steps} />);

  expect(screen.getByRole('progressbar')).toBeInTheDocument();
});

test('it fallback on default job step label when missing', () => {
  const steps: StepExecutionTracking[] = [
    {
      jobName: 'csv_product_export',
      stepName: 'unknown_step',
      status: 'STARTED',
      isTrackable: true,
      hasWarning: false,
      hasError: false,
      duration: 12,
      processedItems: 1,
      totalItems: 100000,
    },
  ];

  renderWithProviders(<JobExecutionProgress steps={steps} />);

  expect(screen.getByText('batch_jobs.default_steps.unknown_step')).toBeInTheDocument();
});

test('it render progress bar lower than 1%', () => {
  const steps: StepExecutionTracking[] = [
    {
      jobName: 'csv_product_export',
      stepName: 'export',
      status: 'STARTED',
      isTrackable: true,
      hasWarning: false,
      hasError: false,
      duration: 12,
      processedItems: 1,
      totalItems: 100000,
    },
  ];

  renderWithProviders(<JobExecutionProgress steps={steps} />);

  expect(screen.getByText('13 day(s) 21 hour(s) left')).toBeInTheDocument();
  expect(screen.getByRole('progressbar')).toHaveAttribute('aria-valuenow', '0.001');
});
