import Attribute, {NormalizedAttribute} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import AttributeCode from 'akeneoenrichedentity/domain/model/attribute/code';
import {denormalizeAttribute} from 'akeneoenrichedentity/domain/model/attribute/attribute';

export interface ListState {
  attributes: NormalizedAttribute[];
}

export default (
  state: ListState = {attributes: []},
  {type, attributes, deletedAttribute}: {type: string; attributes: NormalizedAttribute[]; deletedAttribute: Attribute}
) => {
  switch (type) {
    case 'ATTRIBUTE_LIST_UPDATED':
      state = {...state, attributes};
      break;
    case 'ATTRIBUTE_LIST_ATTRIBUTE_DELETED':
      state = {
        ...state,
        attributes: state.attributes.filter(
          (currentAttribute: NormalizedAttribute) => !denormalizeAttribute(currentAttribute).equals(deletedAttribute)
        ),
      };
      break;
    default:
      break;
  }

  return state;
};
