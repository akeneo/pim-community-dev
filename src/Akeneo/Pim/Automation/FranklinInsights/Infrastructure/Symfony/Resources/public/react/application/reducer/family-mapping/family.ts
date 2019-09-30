import {createReducer} from '../../../infrastructure/create-reducer';
import {
  FamilyActions,
  FETCHED_FAMILY_FAIL,
  FETCHED_FAMILY_SUCCESS,
  FetchedFamilySuccessAction
} from '../../action/family-mapping/family';

export type FamilyState = null | {
  familyCode: string;
  labels: {[locale: string]: string};
};

const initialState: FamilyState = null;

const updateFamily = (_: FamilyState, action: FetchedFamilySuccessAction): FamilyState => {
  return {
    familyCode: action.familyCode,
    labels: action.labels
  };
};

const resetFamily = (): FamilyState => {
  return null;
};

export default createReducer<FamilyState, FamilyActions>(initialState, {
  [FETCHED_FAMILY_SUCCESS]: updateFamily,
  [FETCHED_FAMILY_FAIL]: resetFamily
});
