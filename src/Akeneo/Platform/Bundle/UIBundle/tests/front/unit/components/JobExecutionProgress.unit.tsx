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
    default:
      return key;
  }
});

const JobExecutionProgress = require('pimui/js/job/execution/progress');

test('it render the progress bar of one job step', () => {
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

  expect(screen.getByText('batch_jobs.default_steps.export')).toBeInTheDocument();
  expect(screen.getByText('pim_import_export.tracking.eta.completed')).toBeInTheDocument();
  expect(screen.getByText('100%')).toBeInTheDocument();
});
