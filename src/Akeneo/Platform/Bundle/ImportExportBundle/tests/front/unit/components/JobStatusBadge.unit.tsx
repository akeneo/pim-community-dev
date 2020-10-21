import React from 'react';
import {act, screen} from '@testing-library/react';
import {renderDOMWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import JobExecutionStatus = require("../../../../Resources/public/js/JobStatusBadge");
import {JobStatus} from "../../../../Resources/public/js/useJobExecutionProgress";

declare global {
  namespace NodeJS {
    interface Global {
      fetch: any;
    }
  }
}

let container: HTMLElement;
beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});

afterEach(() => {
  document.body.removeChild(container);
  container = null;
});


test.each<JobStatus>(['COMPLETED', 'STOPPING', 'STOPPED', 'FAILED', 'ABANDONED', 'UNKNOWN'])
('It displays the job progress badge without the progress', (jobStatus) => {

  renderDOMWithProviders(
    <JobExecutionStatus jobExecutionId='123' status={jobStatus}/>,
    container
  );

  expect(screen.getByText(jobStatus)).toBeInTheDocument()
});

test.each<JobStatus>(['STARTING', 'STARTED'])
('It displays the job progress badge with the progress', async (jobStatus) => {
  global.fetch = jest.fn().mockImplementation(async () => {
    return ({
      json: () =>
        Promise.resolve({
          tracking:
            {
              status: 'IN PROGRESS',
              currentStep: 1,
              totalSteps: 10,
              steps: [
                {
                  hasWarning: false,
                  hasError: false
                }
              ]
            },
        }),
    });
  });

  await act(async () => {
    renderDOMWithProviders(
      <JobExecutionStatus jobExecutionId='123' status={jobStatus} />,
      container
    );
  });

  expect(screen.getByText(`${jobStatus} 1/10`)).toBeInTheDocument()
});
