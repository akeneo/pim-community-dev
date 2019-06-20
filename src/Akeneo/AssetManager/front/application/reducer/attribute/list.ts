import {NormalizedAttributeIdentifier} from 'akeneoreferenceentity/domain/model/attribute/identifier';
import {NormalizedAttribute} from 'akeneoreferenceentity/domain/model/attribute/attribute';

export interface ListState {
  attributes: NormalizedAttribute[] | null;
}

export default (
  state: ListState = {attributes: null},
  {
    type,
    attributes,
    deletedAttributeIdentifier,
  }: {
    type: string;
    attributes: NormalizedAttribute[];
    deletedAttributeIdentifier: NormalizedAttributeIdentifier;
  }
) => {
  switch (type) {
    case 'ATTRIBUTE_LIST_UPDATED':
      state = {...state, attributes};
      break;
    case 'ATTRIBUTE_LIST_ATTRIBUTE_DELETED':
      state = {
        ...state,
        attributes:
          null !== state.attributes
            ? state.attributes.filter(
                (currentAttribute: NormalizedAttribute) => currentAttribute.identifier !== deletedAttributeIdentifier
              )
            : null,
      };
      break;
    default:
      break;
  }

  return state;
};
