import {Action} from 'redux';
import {AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';

export type AttributeCode = string;
export type AttributeGroupCode = string;
export type ReferenceDataName = string;
export type Attribute = {
  code: AttributeCode;
  group: AttributeGroupCode;
  is_read_only: boolean;
  reference_data_name: ReferenceDataName;
};

export type StructureState = {
  attributes: Attribute[];
};

export const structureReducer = (state: StructureState = {attributes: []}, action: AttributeListUpdatedAction) => {
  switch (action.type) {
    case 'ATTRIBUTE_LIST_UPDATED':
      state = {...state, attributes: action.attributes};
      break;
    default:
      break;
  }

  return state;
};

type AttributeListUpdatedAction = Action<'ATTRIBUTE_LIST_UPDATED'> & {attributes: Attribute[]};
export const attributeListUpdated = (attributes: Attribute[]) => {
  return {type: 'ATTRIBUTE_LIST_UPDATED', attributes};
};

export const selectAttributeList = (state: AssetCollectionState): Attribute[] => {
  return state.structure.attributes;
};
