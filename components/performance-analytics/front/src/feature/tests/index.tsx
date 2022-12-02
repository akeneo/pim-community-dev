import React, {ReactNode} from 'react';
import {act, render} from '@testing-library/react';
import {DefaultProviders} from '@akeneo-pim-community/shared';
import {Family, FetcherContext} from '../Common';
import {TimeToEnrich} from '../TimeToEnrich';

const weeklyTimeToEnrich: TimeToEnrich[] = [
  {
    code: '2021-W45',
    value: 10,
  },
  {
    code: '2021-W46',
    value: 20,
  },
];

const families = Array.from(Array(100).keys()).map((index: number) => {
  return {code: `family_${index}`, labels: {en_US: `Family ${index}`}};
});

const timeToEnrichByEntity = [
  {
    code: 'accessories',
    value: 44,
  },
  {
    code: 'camcorders',
    value: 10,
  },
  {
    code: 'clothing',
    value: 41,
  },
  {
    code: 'digital_cameras',
    value: 100,
  },
];

const channels = [
  {
    code: 'ecommerce',
    labels: {
      en_US: 'Ecommerce',
    },
  },
  {
    code: 'mobile',
    labels: {
      en_US: 'Mobile',
    },
  },
  {
    code: 'print',
    labels: {
      en_US: 'Print',
    },
  },
];

const locales = [
  {
    code: 'en_US',
    label: 'English',
  },
  {
    code: 'fr_FR',
    label: 'French',
  },
  {
    code: 'de_DE',
    label: 'German',
  },
];

const fetchers = {
  timeToEnrich: {
    fetchHistoricalTimeToEnrich: () => Promise.resolve(weeklyTimeToEnrich),
    fetchAverageTimeToEnrichByEntity: () => Promise.resolve(timeToEnrichByEntity),
  },
  family: {
    fetchFamilies: (limit: number, page: number, search?: string): Promise<{[key: string]: Family}> => {
      const filterFamilies = search
        ? families.filter((family: Family): boolean => family.code.indexOf(search) > 0)
        : families;
      return new Promise(resolve => {
        const paginatedFamilies = filterFamilies.slice((page - 1) * limit, page * limit);
        resolve(
          paginatedFamilies.reduce((map, family: Family) => {
            map[family.code] = family;
            return map;
          }, {})
        );
      });
    },
  },
  channel: {
    fetchChannels: () => Promise.resolve(channels),
  },
  locale: {
    fetchActivatedLocales: () => Promise.resolve(locales),
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
