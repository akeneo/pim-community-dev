import {NormalizedRecordAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/record';
import {NormalizedRecordCollectionAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/record-collection';

const recordAttributeReducer = (
  normalizedAttribute: NormalizedRecordAttribute | NormalizedRecordCollectionAttribute
): NormalizedRecordAttribute | NormalizedRecordCollectionAttribute => {
  // Nothing to edit
  return normalizedAttribute;
};

export const reducer = recordAttributeReducer;
