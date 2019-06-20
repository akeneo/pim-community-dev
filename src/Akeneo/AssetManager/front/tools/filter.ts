import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import {CompletenessValue} from 'akeneoassetmanager/application/component/asset/index/completeness-filter';
import {Attribute} from 'akeneoassetmanager/domain/model/attribute/attribute';

export const getFilter = (filters: Filter[], field: string): Filter | undefined => {
  return filters.find((filter: Filter) => filter.field === field);
};

export const getAttributeFilterKey = (attribute: Attribute) => {
  return `values.${attribute.getIdentifier().stringValue()}`;
};

export const getCompletenessFilter = (filters: Filter[]): CompletenessValue => {
  const filter = getFilter(filters, 'complete');
  const value = undefined !== filter ? filter.value : null;

  switch (value) {
    case true:
      return CompletenessValue.Yes;
    case false:
      return CompletenessValue.No;
    default:
      return CompletenessValue.All;
  }
};
