import {DataMapping} from './DataMapping';
import {CompletenessFilterType, QualityScoreFilterType, CategoryFilterType, EnabledFilterType} from '../components';

type Filter = CompletenessFilterType | QualityScoreFilterType | CategoryFilterType | EnabledFilterType;

type CatalogProjectionCollection = CatalogProjection[];

type CatalogProjection = {
  code: string;
  filters: Filter[];
  dataMappings: DataMapping[];
};

const getDefaultCatalogProjection = (code: string): CatalogProjection => ({
  code,
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
