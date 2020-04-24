import {
  FETCHED_FAMILY_ATTRIBUTES_SUCCESS,
  FETCHED_FAMILY_ATTRIBUTES_FAIL,
  FetchedFamilyAttributesActions,
  FetchedFamilyAttributesSuccessAction,
  SetLoadAttributesAction,
  SET_LOAD_ATTRIBUTES
} from '../../action/family-mapping/family-attributes';
import {Attribute} from '../../../domain/model/attribute';
import {createReducer} from '../../../infrastructure/create-reducer';

export interface FamilyAttributesState {
  attributes: {
    [attributeCode: string]: Attribute;
  };
  loadAttributes: boolean;
}

const initialState: FamilyAttributesState = {
  attributes: {},
  loadAttributes: false
};

const loadAttributes = (state: FamilyAttributesState, action: SetLoadAttributesAction) => ({
  ...state,
  loadAttributes: action.status
});

const updateAttributes = (
  state: FamilyAttributesState,
  action: FetchedFamilyAttributesSuccessAction
): FamilyAttributesState => ({
  ...state,
  loadAttributes: false,
  attributes: action.attributes
});

const resetAttributes = (): FamilyAttributesState => {
  return {attributes: {}, loadAttributes: false};
};

export default createReducer<FamilyAttributesState, FetchedFamilyAttributesActions>(initialState, {
  [FETCHED_FAMILY_ATTRIBUTES_SUCCESS]: updateAttributes,
  [FETCHED_FAMILY_ATTRIBUTES_FAIL]: resetAttributes,
  [SET_LOAD_ATTRIBUTES]: loadAttributes
});
