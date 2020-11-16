import React from 'react';
import {fireEvent, screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {StopJobAction} from '../../../../Resources/public/js/job/execution/StopJobAction';

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
  renderWithProviders(<StopJobAction id="nice-job" jobLabel="Nice job" isStoppable={true} onStop={jest.fn()} />);

  expect(screen.getByText('pim_datagrid.action.stop.title')).toBeInTheDocument();
});

test('It does not render anything if the job is not stoppable', () => {
  renderWithProviders(<StopJobAction id="nice-job" jobLabel="Nice job" isStoppable={false} onStop={jest.fn()} />);

  expect(screen.queryByText('pim_datagrid.action.stop.title')).not.toBeInTheDocument();
});

test('It opens a Modal when clicking on the stop button', () => {
  renderWithProviders(<StopJobAction id="nice-job" jobLabel="Nice job" isStoppable={true} onStop={jest.fn()} />);

  fireEvent.click(screen.getByText('pim_datagrid.action.stop.title'));

  expect(screen.getByText('pim_datagrid.action.stop.confirmation.title')).toBeInTheDocument();
});

test('It fetches the correct route & calls the refreshCollection handler when clicking on the stop button', async () => {
  const mockFetch = jest.fn().mockImplementationOnce(() =>
    Promise.resolve({
      ok: true,
      json: () => ({successful: true}),
    })
  );

  global.fetch = mockFetch;

  const onStop = jest.fn();

  renderWithProviders(<StopJobAction id="nice-job" jobLabel="Nice job" isStoppable={true} onStop={onStop} />);

  //Opening the modal
  fireEvent.click(screen.getByText('pim_datagrid.action.stop.title'));

  //Clicking on the confirm button
  await fireEvent.click(screen.getByText('pim_datagrid.action.stop.confirmation.ok'));

  expect(mockFetch).toBeCalledWith('pim_enrich_job_tracker_rest_stop');
  expect(onStop).toBeCalledTimes(1);
});
