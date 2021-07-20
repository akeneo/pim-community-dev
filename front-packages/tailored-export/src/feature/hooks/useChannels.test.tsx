import React from 'react';
import {renderHook, act} from '@testing-library/react-hooks';
import {Channel} from '@akeneo-pim-community/shared';
import {FetcherContext} from '../contexts';
import {useChannels} from './useChannels';
import {AssociationType, Attribute} from '../models';

const channelResponse: Channel[] = [
  {
    code: 'ecommerce',
    locales: [
      {
        code: 'en_US',
        label: 'English (United States)',
        language: '',
        region: '',
      },
      {
        code: 'fr_FR',
        label: 'French (France)',
        language: '',
        region: '',
      },
    ],
    labels: {
      fr_FR: 'Ecommerce',
    },
  },
  {
    code: 'mobile',
    locales: [
      {
        code: 'de_DE',
        label: 'German (Germany)',
        language: '',
        region: '',
      },
      {
        code: 'en_US',
        label: 'English (United States)',
        language: '',
        region: '',
      },
    ],
    labels: {
      fr_FR: 'Mobile',
    },
  },
  {
    code: 'print',
    locales: [
      {
        code: 'de_DE',
        label: 'German (Germany)',
        language: '',
        region: '',
      },
      {
        code: 'en_US',
        label: 'English (United States)',
        language: '',
        region: '',
      },
      {
        code: 'fr_FR',
        label: 'French (France)',
        language: '',
        region: '',
      },
    ],
    labels: {
      fr_FR: 'Impression',
    },
  },
];

const fetchers = {
  attribute: {
    fetchByIdentifiers: (): Promise<Attribute[]> => Promise.resolve<Attribute[]>([]),
  },
  channel: {
    fetchAll: (): Promise<Channel[]> => Promise.resolve(channelResponse),
  },
  associationType: {fetchByCodes: (): Promise<AssociationType[]> => Promise.resolve([])},
};

const Wrapper: React.FC = ({children}) => {
  return <FetcherContext.Provider value={fetchers}>{children}</FetcherContext.Provider>;
};

test('It fetches channels', async () => {
  const {result, waitForNextUpdate} = renderHook(() => useChannels(), {wrapper: Wrapper});
  expect(result.current);
  await act(async () => {
    await waitForNextUpdate();
  });

  const attributes = result.current;
  expect(attributes).toEqual(channelResponse);
});

test('It does not set state when unmounted', async () => {
  const {result, unmount} = renderHook(() => useChannels(), {wrapper: Wrapper});

  unmount();

  const attributes = result.current;
  expect(attributes).toEqual([]);
});
