import {createContext, useContext} from 'react';
import {Channel} from '@akeneo-pim-community/shared';
import {Attribute, MeasurementFamily} from '../models';

type FetcherValue = {
  attribute: {
    fetchByIdentifiers: (identifiers: string[]) => Promise<Attribute[]>;
    fetchAttributeIdentifier: () => Promise<Attribute>;
  };
  channel: {
    fetchAll: () => Promise<Channel[]>;
  };
  measurementFamily: {
    fetchByCode: (code: string) => Promise<MeasurementFamily | undefined>;
  };
};

const FetcherContext = createContext<FetcherValue>({
  attribute: {
    fetchByIdentifiers: () => {
      throw new Error('Fetch attributes by identifiers needs to be implemented');
    },
    fetchAttributeIdentifier: () => {
      throw new Error('Fetch attribute identifier needs to be implemented');
    },
  },
  channel: {
    fetchAll: () => {
      throw new Error('Fetch all channels needs to be implemented');
    },
  },
  measurementFamily: {
    fetchByCode: () => {
      throw new Error('Fetch measurement family by code needs to be implemented');
    },
  },
});

const useFetchers = (): FetcherValue => {
  const fetchers = useContext(FetcherContext);

  return fetchers;
};

export {FetcherContext, useFetchers};
