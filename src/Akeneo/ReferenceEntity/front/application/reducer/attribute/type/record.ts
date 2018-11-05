import {NormalizedRecordAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/record';

const recordAttributeReducer = (normalizedAttribute: NormalizedRecordAttribute): NormalizedRecordAttribute => {
  // Nothing to edit
  return normalizedAttribute;
};

export const reducer = recordAttributeReducer;
