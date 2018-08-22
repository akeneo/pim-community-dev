import Attribute, {NormalizedAttribute} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import AttributeCode from 'akeneoenrichedentity/domain/model/attribute/code';
import {denormalizeAttribute} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import {denormalizeIdentifier} from 'akeneoenrichedentity/domain/model/attribute/identifier';

export interface ListState {
  attributes: NormalizedAttribute[];
}

export default (
  state: ListState = {attributes: []},
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
        attributes: state.attributes.filter(
          (currentAttribute: NormalizedAttribute) =>
            !denormalizeIdentifier(currentAttribute.identifier).equals(
              denormalizeIdentifier(deletedAttribute.identifier)
            )
        ),
      };
      break;
    default:
      break;
  }

  return state;
};
