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
    ReactDOM.render(this.reactElementToMount(), this.el);
  }

  getRoot() {
    return {
      getFormData: getFormData,
    };
  }
}

jest.mock('@akeneo-pim-community/legacy-bridge/src/bridge/react', () => ({ReactView: BaseViewMock}));

const JobExecutionStatus = require('pimui/js/job/execution/status');

test('it render a job execution status with the correct label', () => {
  getFormData.mockImplementationOnce(() => ({
    tracking: {
      status: 'COMPLETED',
      currentStep: 1,
      totalSteps: 1,
      steps: [
        {
          jobName: 'csv_product_export',
          stepName: 'export',
        },
      ],
    },
  }));

  const component = new JobExecutionStatus(container);
  component.render();

  expect(screen.getByText('pim_common.status')).toBeInTheDocument();
  expect(screen.getByText('pim_import_export.job_status.COMPLETED')).toBeInTheDocument();
});
