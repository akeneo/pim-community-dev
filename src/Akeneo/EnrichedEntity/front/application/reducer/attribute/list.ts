import {NormalizedAttribute} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import {denormalizeIdentifier} from 'akeneoenrichedentity/domain/model/attribute/identifier';

export interface ListState {
  attributes: NormalizedAttribute[] | null;
}

export default (
  state: ListState = {attributes: null},
  {
    type,
    attributes,
    deletedAttribute,
  }: {type: string; attributes: NormalizedAttribute[]; deletedAttribute: NormalizedAttribute}
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
                (currentAttribute: NormalizedAttribute) =>
                  !denormalizeIdentifier(currentAttribute.identifier).equals(
                    denormalizeIdentifier(deletedAttribute.identifier)
                  )
              )
            : null,
      };
      break;
    default:
      break;
  }

  return state;
};
