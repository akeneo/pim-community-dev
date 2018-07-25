import {NormalizedAttribute} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import AttributeCode from 'akeneoenrichedentity/domain/model/attribute/code';

export interface ListState {
  attributes: NormalizedAttribute[];
  openedAttribute: AttributeCode | null;
}

export default (
  state: ListState = {attributes: [], openedAttribute: null},
  {type, attributes}: {type: string; attributes: NormalizedAttribute[]}
) => {
  switch (type) {
    case 'ATTRIBUTE_LIST_UPDATED':
      state = {...state, attributes};
      break;
    default:
      break;
  }

  return state;
};
