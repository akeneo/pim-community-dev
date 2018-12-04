import {Filter} from 'akeneoreferenceentity/application/reducer/grid';
import {CompletenessValue} from "akeneoreferenceentity/application/component/record/index/completeness-filter";

class InvalidArgumentError extends Error {}

/**
 * @todo This method should be moved in a Query Object on the Grid but to do that we need to create this Query Object first. It will be done in a next PR.
 */
export const getFilter = (filters: Filter[], field: string): Filter => {
  const filter = filters.find((filter: Filter) => filter.field === field);

  if (!filter) {
    throw new InvalidArgumentError(`The query needs to contains a filter on the "${field}" field`);
  }

  return filter;
};

export const getCompletenessFilter = (filters: Filter[]): CompletenessValue => {
  let completenessValue: CompletenessValue = CompletenessValue.All;

  const filter = filters.find((filter: Filter) => filter.field === 'complete');
  if (filter) {
    switch (filter.value) {
      case true:
        completenessValue = CompletenessValue.Yes;
        break;
      case false :
        completenessValue = CompletenessValue.No;
        break;
    }
  }

  return completenessValue;
}
