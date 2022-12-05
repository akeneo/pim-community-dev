import {DataMapping} from './DataMapping';
import {uuid} from 'akeneo-design-system';

type Filter = {
  field: string;
  operator: string;
  value: string;
  uuid?: string;
  context?: {
    locale?: string;
    scope?: string;
  };
};
type CatalogProjectionCollection = CatalogProjection[];

type CatalogProjection = {
  uuid: string;
  code: string;
  label: string;
  filters: Filter[];
  dataMappings: DataMapping[];
};

const getDefaultCatalogProjection = (code: string): CatalogProjection => ({
  uuid: uuid(),
  code,
  label: code,
  filters: [],
  dataMappings: [],
});

const updateCatalogProjectionFilter = (catalogProjection: CatalogProjection, filters: Filter[]) => {
  return {
    ...catalogProjection,
    filters,
  };
};

const updateCatalogProjectionDataMapping = (catalogProjection: CatalogProjection, dataMapping: DataMapping) => {
  const updatedDataMappings = [
    ...catalogProjection.dataMappings.filter(({target}) => target.name === dataMapping.target.name),
    dataMapping,
  ];

  return {
    ...catalogProjection,
    dataMappings: updatedDataMappings,
  };
};

export type {CatalogProjectionCollection, CatalogProjection, Filter};
export {getDefaultCatalogProjection, updateCatalogProjectionDataMapping, updateCatalogProjectionFilter};
