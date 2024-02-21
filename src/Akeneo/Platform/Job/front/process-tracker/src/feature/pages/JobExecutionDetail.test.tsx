import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import {JobExecutionDetail} from './JobExecutionDetail';
import {JobExecution} from '../models';
import userEvent from '@testing-library/user-event';

beforeEach(() => {
  jest.resetModules();
});

const jobExecution: JobExecution = {
  jobInstance: {
    code: 'csv_product_export',
    label: 'Demo CSV product export',
    type: 'other',
  },
  status: 'success',
  isRunning: false,
  failures: [],
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
};

jest.mock('../hooks/useJobExecution', () => ({
  useJobExecution: (jobExecutionId: string) => {
    switch (jobExecutionId) {
      case '23':
        return [null, null, () => {}, false];
      case '24':
        return [jobExecution, null, () => {}, false];
      case '25':
        return [{...jobExecution, jobInstance: {...jobExecution.jobInstance, type: 'export'}}, null, () => {}, false];
      case '26':
        return [{...jobExecution, jobInstance: {...jobExecution.jobInstance, type: 'import'}}, null, () => {}, false];
      case '27':
        return [{...jobExecution, isRunning: true}, null, () => {}, false];
      case '28':
        return [
          null,
          {
            statusMessage: '404',
            statusCode: 'not found',
          },
          () => {},
          false,
        ];
      default:
        return [null, null, () => {}, false];
    }
  },
}));

jest.mock('@akeneo-pim-community/shared/lib/components/PimView', () => ({
  PimView: () => <></>,
}));

const mockStopJobExecution = jest.fn();
jest.mock('../components/common/StopJobAction', () => ({
  StopJobAction: ({onStop}: {onStop: () => void}) => (
    <div
      onClick={() => {
        mockStopJobExecution();
        onStop();
      }}
    >
      stop job
    </div>
  ),
}));

test('it renders the job execution detail page without a job', () => {
  renderWithProviders(<JobExecutionDetail jobExecutionId="23" />);

  expect(screen.getByText('pim_menu.tab.activity')).toBeInTheDocument();
  expect(screen.getByText('pim_menu.item.job_tracker')).toBeInTheDocument();
});
test('it renders the job execution detail page', () => {
  renderWithProviders(<JobExecutionDetail jobExecutionId="24" />);

  expect(screen.getByText('pim_menu.tab.activity')).toBeInTheDocument();
  expect(screen.getByText('pim_menu.item.job_tracker')).toBeInTheDocument();
  expect(screen.getByText('akeneo_job.job_status.IN_PROGRESS 1/1')).toBeInTheDocument();
});

test('it renders the job execution export detail page', () => {
  renderWithProviders(<JobExecutionDetail jobExecutionId="25" />);

  expect(screen.getByText('pim_menu.tab.activity')).toBeInTheDocument();
  expect(screen.getByText('pim_menu.item.job_tracker')).toBeInTheDocument();
  expect(screen.getByText('akeneo_job.job_status.IN_PROGRESS 1/1')).toBeInTheDocument();
});

test('it renders the job execution import detail page', () => {
  renderWithProviders(<JobExecutionDetail jobExecutionId="26" />);

  expect(screen.getByText('pim_menu.tab.activity')).toBeInTheDocument();
  expect(screen.getByText('pim_menu.item.job_tracker')).toBeInTheDocument();
  expect(screen.getByText('akeneo_job.job_status.IN_PROGRESS 1/1')).toBeInTheDocument();
});

test('it stops the job execution', () => {
  renderWithProviders(<JobExecutionDetail jobExecutionId="26" />);

  expect(screen.getByText('pim_menu.tab.activity')).toBeInTheDocument();
  expect(screen.getByText('pim_menu.item.job_tracker')).toBeInTheDocument();
  expect(screen.getByText('stop job')).toBeInTheDocument();
  expect(mockStopJobExecution).not.toHaveBeenCalled();
  userEvent.click(screen.getByText('stop job'));
  expect(mockStopJobExecution).toHaveBeenCalled();
});

test('it can show every downloadable files', () => {
  renderWithProviders(<JobExecutionDetail jobExecutionId="27" />);

  expect(screen.getByText('pim_menu.tab.activity')).toBeInTheDocument();
  expect(screen.getByText('pim_menu.item.job_tracker')).toBeInTheDocument();
  expect(screen.getByText('pim_enrich.entity.job_execution.module.download.dropdown_title')).toBeInTheDocument();
  userEvent.click(screen.getByText('pim_enrich.entity.job_execution.module.download.dropdown_title'));
  expect(screen.getByText('pim_enrich.entity.job_execution.module.download.archive')).toBeInTheDocument();
});

test('it displays an error if needed', () => {
  renderWithProviders(<JobExecutionDetail jobExecutionId="28" />);

  expect(screen.getByText('not found')).toBeInTheDocument();
  expect(screen.getByText('404')).toBeInTheDocument();
});
