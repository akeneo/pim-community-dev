import React from 'react';
import {fireEvent, screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {StopJobAction} from '../../../../../../../../../public/bundles/pimui/js/job/execution/StopJobAction';

declare global {
  namespace NodeJS {
    interface Global {
      fetch: any;
    }
  }
}

afterEach(() => {
  global.fetch && global.fetch.mockClear();
  delete global.fetch;
});

test('It renders the stop button correctly if the job is stoppable', () => {
  renderWithProviders(<StopJobAction id="nice-job" isStoppable={true} refresh={jest.fn()} />);

  expect(screen.getByText('pim_datagrid.action.stop.title')).toBeInTheDocument();
});

test('It does not render anything if the job is not stoppable', () => {
  renderWithProviders(<StopJobAction id="nice-job" isStoppable={false} refresh={jest.fn()} />);

  expect(screen.queryByText('pim_datagrid.action.stop.title')).not.toBeInTheDocument();
});

test('It fetches the correct route & calls the refresh handler when clicking on the stop button', async () => {
  const mockFetch = jest.fn().mockImplementationOnce(() =>
    Promise.resolve({
      ok: true,
      json: () => ({successful: true}),
    })
  );

  global.fetch = mockFetch;

  const refresh = jest.fn();

  renderWithProviders(<StopJobAction id="nice-job" isStoppable={true} refresh={refresh} />);

  await fireEvent.click(screen.getByText('pim_datagrid.action.stop.title'));

  expect(mockFetch).toBeCalledWith('pim_enrich_job_tracker_rest_stop');
  expect(refresh).toBeCalledTimes(1);
});
