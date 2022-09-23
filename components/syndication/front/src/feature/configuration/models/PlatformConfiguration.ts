import {CatalogProjectionCollection} from './CatalogProjection';

type PlatformConfiguration = {
  connection: {
    connectedChannelCode: string;
  };
  catalogProjections: CatalogProjectionCollection;
};

export type {PlatformConfiguration};
