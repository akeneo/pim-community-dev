import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import JobExecutionStatus, {JobStatus} from "../../../../Resources/public/js/JobExecutionStatus";

declare global {
  namespace NodeJS {
    interface Global {
      fetch: any;
    }
  }
}

test.each<JobStatus>(['COMPLETED', 'STOPPING', 'STOPPED', 'FAILED', 'ABANDONED', 'UNKNOWN'])
('It displays the job status "%s" without the progress', (jobStatus) => {

  renderWithProviders(
    <JobExecutionStatus status={jobStatus} currentStep={1} totalSteps={3} hasError={false} hasWarning={false} />
  );

  expect(screen.getByText(jobStatus)).toBeInTheDocument()
});

test.each<JobStatus>(['STARTING', 'STARTED'])
('It displays the job status "%s" with the progress', (jobStatus) => {
  const currentStep = 1;
  const totalSteps = 10;

  renderWithProviders(
    <JobExecutionStatus status={jobStatus} currentStep={currentStep} totalSteps={totalSteps} hasError={false}
                        hasWarning={false}/>
  );

  expect(screen.getByText(`${jobStatus} ${currentStep}/${totalSteps}`)).toBeInTheDocument()
});
