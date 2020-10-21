import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {renderHook} from '@testing-library/react-hooks';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {JobStatus, useJobExecutionProgress} from "../../../../Resources/public/js/useJobExecutionProgress";

declare global {
  namespace NodeJS {
    interface Global {
      fetch: any;
    }
  }
}

// TODO: Should I move this to the custom hook file ?
type backendJobExecutionProgress = { tracking: { currentStep: number; totalSteps: number; steps: ({ hasWarning: boolean; hasError: boolean })[]; status: string } };

const wrapper = ({children}) => <DependenciesProvider>{children}</DependenciesProvider>;
afterEach(() => {
  global.fetch && global.fetch.mockClear();
  delete global.fetch;
});

const mockFetchJobStatusProgress = (jobExecutionProgress: backendJobExecutionProgress) => {
  global.fetch = jest.fn().mockImplementation(async () => {
    return ({
      json: () =>
        Promise.resolve(jobExecutionProgress),
    });
  });
}

test('It returns the fetched job execution status', async () => {
  const jobExecutionProgress = {
    tracking:
      {
        status: 'IN PROGRESS',
        currentStep: 1,
        totalSteps: 10,
        steps: [
          {
            hasWarning: false,
            hasError: false
          },
        ]
      },
  };
  mockFetchJobStatusProgress(jobExecutionProgress);

  const {result, waitForNextUpdate} = renderHook(() => useJobExecutionProgress('1', 'STARTING'), {wrapper});
  await waitForNextUpdate();

  expect(result.current).toEqual({
    status: 'STARTING',
    currentStep: 1,
    totalSteps: 10,
    hasWarning: false,
    hasError: false,
  });
});

test.each<JobStatus>(['COMPLETED', 'STOPPING', 'STOPPED', 'FAILED', 'ABANDONED', 'UNKNOWN'])
('It does not fetch the progress if the job status is not started', (jobStatusNotTriggeringFetch: JobStatus) => {
  global.fetch = jest.fn();

  const {result} = renderHook(() => useJobExecutionProgress('1', jobStatusNotTriggeringFetch), {wrapper});

  expect(global.fetch).not.toBeCalled();
  expect(result.current).toEqual({
    status: jobStatusNotTriggeringFetch,
    currentStep: 0,
    totalSteps: 0,
    hasWarning: false,
    hasError: false,
  });
});

test('It tells if the job execution has errors or warnings', async () => {
  const jobExecutionProgress = {
    tracking:
      {
        status: 'IN PROGRESS',
        currentStep: 1,
        totalSteps: 10,
        steps: [
          {
            hasWarning: false,
            hasError: false
          },
          {
            hasWarning: true,
            hasError: true
          },
          {
            hasWarning: false,
            hasError: false
          },
        ]
      },
  };
  mockFetchJobStatusProgress(jobExecutionProgress);

  const {result, waitForNextUpdate} = renderHook(() => useJobExecutionProgress('1', 'STARTING'), {wrapper});
  await waitForNextUpdate();

  expect(result.current).toEqual({
    status: 'STARTING',
    currentStep: 1,
    totalSteps: 10,
    hasWarning: true,
    hasError: true,
  });
});
