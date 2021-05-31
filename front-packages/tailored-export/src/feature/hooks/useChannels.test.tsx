import React from "react";
import {act} from 'react-dom/test-utils';
import {renderHook} from '@testing-library/react-hooks';
import {Channel} from '@akeneo-pim-community/shared';
import {Attribute, FetcherContext} from '../contexts';
import {useChannels} from "./useChannels";

const channelResponse: Channel[] = [
  {
    code: 'ecommerce',
    locales: [
      {
        code: 'en_US',
        label: 'English (United States)',
        language: '',
        region: ''
      },
      {
        code: 'fr_FR',
        label: 'French (France)',
        language: '',
        region: ''
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
        region: ''
      },
      {
        code: 'en_US',
        label: 'English (United States)',
        language: '',
        region: ''
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
        region: ''
      },
      {
        code: 'en_US',
        label: 'English (United States)',
        language: '',
        region: ''
      },
      {
        code: 'fr_FR',
        label: 'French (France)',
        language: '',
        region: ''
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
    fetchAll: (): Promise<Channel[]> => new Promise(resolve => resolve(channelResponse)),
  },
};

const Wrapper: React.FC = ({children}) => {
  return (
    <FetcherContext.Provider value={fetchers}>{children}</FetcherContext.Provider>
  )
};

test('It fetch the channels', async () => {
  const {result, waitForNextUpdate} = renderHook(() => useChannels(), {wrapper: Wrapper});
  expect(result.current);
  await act(async () => {
    await waitForNextUpdate();
  });

  const attributes = result.current;
  expect(attributes).toEqual(channelResponse);
});
