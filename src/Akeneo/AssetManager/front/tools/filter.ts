import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';

export const getFilter = (filters: Filter[], field: string): Filter | undefined => {
  return filters.find((filter: Filter) => filter.field === field);
};

export const getAttributeFilterKey = (attribute: NormalizedAttribute) => {
  return `values.${attribute.identifier}`;
};
