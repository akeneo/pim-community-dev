import {
  FETCHED_FAMILY_ATTRIBUTES_SUCCESS,
  FETCHED_FAMILY_ATTRIBUTES_FAIL,
  FetchedFamilyAttributesActions,
  FetchedFamilyAttributesSuccessAction
} from '../../action/family-mapping/family-attributes';
import {Attribute} from '../../../domain/model/attribute';
import {createReducer} from '../../../infrastructure/create-reducer';

export interface FamilyAttributesState {
  [attributeCode: string]: Attribute;
}

const initialState: FamilyAttributesState = {};

const updateAttributes = (
  _: FamilyAttributesState,
  action: FetchedFamilyAttributesSuccessAction
): FamilyAttributesState => {
  return action.attributes;
};

const resetAttributes = (): FamilyAttributesState => {
  return {};
};

export default createReducer<FamilyAttributesState, FetchedFamilyAttributesActions>(initialState, {
  [FETCHED_FAMILY_ATTRIBUTES_SUCCESS]: updateAttributes,
  [FETCHED_FAMILY_ATTRIBUTES_FAIL]: resetAttributes
});
