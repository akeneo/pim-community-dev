import {createContext, useContext} from 'react';
import {Channel} from '@akeneo-pim-community/shared';
import {Attribute, AssociationType, MeasurementFamily} from '../models';

type FetcherValue = {
  attribute: {
    fetchByIdentifiers: (identifiers: string[]) => Promise<Attribute[]>;
  };
  channel: {
    fetchAll: () => Promise<Channel[]>;
  };
  associationType: {
    fetchByCodes: (codes: string[]) => Promise<AssociationType[]>;
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
  },
  channel: {
    fetchAll: () => {
      throw new Error('Fetch all channels needs to be implemented');
    },
  },
  associationType: {
    fetchByCodes: () => {
      throw new Error('Fetch association types by codes needs to be implemented');
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
