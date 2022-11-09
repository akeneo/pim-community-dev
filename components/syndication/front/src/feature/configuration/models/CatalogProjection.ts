import {DataMapping} from './DataMapping';
import {CompletenessFilterType, QualityScoreFilterType, CategoryFilterType, EnabledFilterType} from '../components';
import {uuid} from 'akeneo-design-system';

type Filter = CompletenessFilterType | QualityScoreFilterType | CategoryFilterType | EnabledFilterType;

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

const updateCatalogProjectionFilter = (catalogProjection: CatalogProjection, filter: Filter) => {
  const updatedFilters = [...catalogProjection.filters.filter(({field}) => field === filter.field), filter];

  return {
    ...catalogProjection,
    filters: updatedFilters,
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
