import React from 'react';
import {fireEvent, screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import Actions from '../../../../Resources/public/js/datagrid/Actions';

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

test('It renders the job Actions correctly, with the stop button if the job is stoppable', () => {
  renderWithProviders(<Actions id="nice-job" jobLabel="Nice job" isStoppable={true} refreshCollection={jest.fn()} />);

  expect(screen.getByText('pim_datagrid.action.show.title')).toBeInTheDocument();
  expect(screen.getByText('pim_datagrid.action.stop.title')).toBeInTheDocument();
});

test('It does not display the stop button if the job is not stoppable', () => {
  renderWithProviders(<Actions id="nice-job" jobLabel="Nice job" isStoppable={false} refreshCollection={jest.fn()} />);

  expect(screen.queryByText('pim_datagrid.action.stop.title')).not.toBeInTheDocument();
});

test('It opens a Modal when clicking on the stop button', () => {
  renderWithProviders(<Actions id="nice-job" jobLabel="Nice job" isStoppable={true} refreshCollection={jest.fn()} />);

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

  const refreshCollection = jest.fn();

  renderWithProviders(
    <Actions id="nice-job" jobLabel="Nice job" isStoppable={true} refreshCollection={refreshCollection} />
  );

  //Opening the modal
  fireEvent.click(screen.getByText('pim_datagrid.action.stop.title'));

  //Clicking on the confirm button
  await fireEvent.click(screen.getByText('pim_datagrid.action.stop.confirmation.ok'));

  expect(mockFetch).toBeCalledWith('pim_enrich_job_tracker_rest_stop');
  expect(refreshCollection).toBeCalledTimes(1);
});
