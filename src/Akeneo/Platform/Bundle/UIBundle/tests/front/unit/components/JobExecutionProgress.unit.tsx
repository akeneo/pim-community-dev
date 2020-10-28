import {screen} from '@testing-library/react';
import '@testing-library/jest-dom';

let container: HTMLElement;
beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});
afterEach(() => {
  document.body.removeChild(container);
});

const mockGetFormData = jest.fn();

class BaseViewMock {
  el: HTMLElement;

  constructor(container: HTMLElement) {
    this.el = container;
  }

  getRoot() {
    return {
      getFormData: mockGetFormData,
    };
  }
}

jest.mock('pimui/js/view/base', () => (BaseViewMock));
jest.mock('oro/translator', () => (key: string, _params: any, count: number): string => {
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
    default:
      return key;
  }
});

const JobExecutionProgress = require('pimui/js/job/execution/progress');

test('it render a progress bar with the correct label of the job step', () => {
  mockGetFormData.mockImplementationOnce(() => ({
    'tracking': {
      'status': 'COMPLETED',
      'currentStep': 1,
      'totalSteps': 1,
      'steps': [
        {
          'jobName': 'csv_product_export',
          'stepName': 'export',
        },
      ],
    },
  }));

  const component = new JobExecutionProgress(container);
  component.render();

  expect(screen.getByText('Product export')).toBeInTheDocument();
});

test('it render the progress bar of one completed job step', () => {
  mockGetFormData.mockImplementationOnce(() => ({
    'tracking': {
      'status': 'COMPLETED',
      'currentStep': 1,
      'totalSteps': 1,
      'steps': [
        {
          'jobName': 'csv_product_export',
          'stepName': 'export',
          'status': 'COMPLETED',
          'isTrackable': true,
          'hasWarning': false,
          'hasError': false,
          'duration': 0,
          'processedItems': 0,
          'totalItems': 0,
        },
      ],
    },
  }));

  const component = new JobExecutionProgress(container);
  component.render();

  expect(screen.getByText('pim_import_export.tracking.completed')).toBeInTheDocument();
  expect(screen.getByRole('progressbar')).toHaveAttribute('aria-valuenow', '100');
});

test('it render the progress bar of one job step in progress', () => {
  mockGetFormData.mockImplementationOnce(() => ({
    'tracking': {
      'status': 'IN_PROGRESS',
      'currentStep': 1,
      'totalSteps': 1,
      'steps': [
        {
          'jobName': 'csv_product_export',
          'stepName': 'export',
          'status': 'IN_PROGRESS',
          'isTrackable': true,
          'hasWarning': false,
          'hasError': false,
          'duration': 10,
          'processedItems': 50,
          'totalItems': 100,
        },
      ],
    },
  }));

  const component = new JobExecutionProgress(container);
  component.render();

  expect(screen.getByText('pim_import_export.tracking.in_progress')).toBeInTheDocument();
  expect(screen.getByRole('progressbar')).toHaveAttribute('aria-valuenow', '50');
});

test('it render the progress bar of one pending job step', () => {
  mockGetFormData.mockImplementationOnce(() => ({
    'tracking': {
      'status': 'NOT_STARTED',
      'currentStep': 1,
      'totalSteps': 1,
      'steps': [
        {
          'jobName': 'csv_product_export',
          'stepName': 'export',
          'status': 'NOT_STARTED',
          'isTrackable': true,
          'hasWarning': false,
          'hasError': false,
          'duration': 0,
          'processedItems': 0,
          'totalItems': 0,
        },
      ],
    },
  }));

  const component = new JobExecutionProgress(container);
  component.render();

  expect(screen.getByText('pim_import_export.tracking.not_started')).toBeInTheDocument();
  expect(screen.getByRole('progressbar')).toHaveAttribute('aria-valuenow', '0');
});

test('it render the progress bar of one untrackable job step', () => {
  mockGetFormData.mockImplementationOnce(() => ({
    'tracking': {
      'status': 'NOT_STARTED',
      'currentStep': 1,
      'totalSteps': 1,
      'steps': [
        {
          'jobName': 'csv_product_export',
          'stepName': 'export',
          'status': 'NOT_STARTED',
          'isTrackable': false,
          'hasWarning': false,
          'hasError': false,
          'duration': 0,
          'processedItems': 0,
          'totalItems': 0,
        },
      ],
    },
  }));

  const component = new JobExecutionProgress(container);
  component.render();

  expect(screen.getByText('pim_import_export.tracking.untrackable')).toBeInTheDocument();
  expect(screen.getByRole('progressbar')).not.toHaveAttribute('aria-valuenow');
});
