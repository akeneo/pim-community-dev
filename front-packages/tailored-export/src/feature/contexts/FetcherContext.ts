import {createContext, useContext} from 'react';
import {Channel} from '@akeneo-pim-community/shared';
import {Attribute} from '../models';

type FetcherValue = {
  attribute: {
    fetchByIdentifiers: (identifiers: string[]) => Promise<Attribute[]>;
  };
  channel: {
    fetchAll: () => Promise<Channel[]>;
  };
};

const FetcherContext = createContext<FetcherValue>({
  attribute: {
    fetchByIdentifiers: () => {
      throw new Error('Fetch attributes by identifiers needs to be implemented');
    },
  },
  channel: {
    fetchAll: () => {
      throw new Error('Fetch all channels needs to be implemented');
    },
  },
});

const useFetchers = (): FetcherValue => {
  const fetchers = useContext(FetcherContext);

  return fetchers;
};

export {FetcherContext, useFetchers};
export type {Attribute};
