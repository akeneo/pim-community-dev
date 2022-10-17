import React, {ReactNode} from 'react';
import {act, render} from '@testing-library/react';
import {DefaultProviders} from '@akeneo-pim-community/shared';
import {FetcherContext} from '../Common';

const weeklyTimeToEnrich = [
  {
    period: '2021-W45',
    value: 10,
  },
  {
    period: '2021-W46',
    value: 20,
  },
];

const fetchers = {
  timeToEnrich: {
    fetchHistoricalTimeToEnrich: () => Promise.resolve(weeklyTimeToEnrich),
  },
};

const renderWithProviders = async (children: ReactNode) => {
  return await act(
    async () =>
      void render(
        <DefaultProviders>
          <FetcherContext.Provider value={fetchers}>{children}</FetcherContext.Provider>
        </DefaultProviders>
      )
  );
};

export {renderWithProviders};
