import {NormalizedOptionAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/option';
import {NormalizedOptionCollectionAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/option-collection';

const optionAttributeReducer = (
  normalizedAttribute: NormalizedOptionAttribute | NormalizedOptionCollectionAttribute
): NormalizedOptionAttribute | NormalizedOptionCollectionAttribute => {
  // Nothing to edit
  return normalizedAttribute;
};

export const reducer = optionAttributeReducer;
