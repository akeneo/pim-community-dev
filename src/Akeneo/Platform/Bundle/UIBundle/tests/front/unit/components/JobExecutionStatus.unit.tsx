import ReactDOM from 'react-dom';
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

const getFormData = jest.fn();

abstract class BaseViewMock {
  el: HTMLElement;

  constructor(container: HTMLElement) {
    this.el = container;
  }

  abstract reactElementToMount(): JSX.Element;

  render() {
    ReactDOM.render(
      this.reactElementToMount(),
      this.el
    );
  }

  getRoot() {
    return {
      getFormData: getFormData,
    };
  }
}

jest.mock('@akeneo-pim-community/legacy-bridge/src/bridge/react', () => ({ReactView: BaseViewMock}));

// const translator = jest.fn().mockImplementation((key: string, _params: any, count: number): string => {
//   switch (key) {
//     case 'duration.days':
//       return `${count} day(s)`;
//     case 'duration.hours':
//       return `${count} hour(s)`;
//     case 'duration.minutes':
//       return `${count} minute(s)`;
//     case 'duration.seconds':
//       return `${count} second(s)`;
//     case 'batch_jobs.csv_product_export.export.label':
//       return 'Product export';
//     default:
//       return key;
//   }
// });
//
// jest.mock('oro/translator', () => translator);

const JobExecutionStatus = require('pimui/js/job/execution/status');

test('it render a progress bar with the correct label of the job step', () => {
  getFormData.mockImplementationOnce(() => ({
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

  const component = new JobExecutionStatus(container);
  component.render();

  expect(screen.getByText('pim_common.status')).toBeInTheDocument();
  expect(screen.getByText('COMPLETED')).toBeInTheDocument();
});
