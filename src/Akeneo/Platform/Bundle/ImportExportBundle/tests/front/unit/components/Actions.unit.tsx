import React from 'react';
import {screen} from '@testing-library/react';
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
  renderWithProviders(
    <Actions
      id="nice-job"
      showLink="https://akeneo.com"
      jobLabel="Nice job"
      isStoppable={true}
      refreshCollection={jest.fn()}
    />
  );

  expect(screen.getByText('pim_datagrid.action.show.title')).toBeInTheDocument();
  expect(screen.getByText('pim_datagrid.action.stop.title')).toBeInTheDocument();
});

test('It does not display the stop button if the job is not stoppable', () => {
  renderWithProviders(
    <Actions
      id="nice-job"
      showLink="https://akeneo.com"
      jobLabel="Nice job"
      isStoppable={false}
      refreshCollection={jest.fn()}
    />
  );

  expect(screen.queryByText('pim_datagrid.action.stop.title')).not.toBeInTheDocument();
});
