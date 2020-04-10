import {fetchAttributesByFamily} from '../../../infrastructure/fetcher/attributes';
import {Attribute} from '../../../domain/model/attribute';
import {fetchAttributeGroups} from './attribute-group';

export function fetchAttributes(familyCode: string) {
  return async (dispatch: any) => {
    try {
      dispatch(setLoadAttributes(true));
      const attributes = await fetchAttributesByFamily(familyCode);
      dispatch(setLoadAttributes(false));
      dispatch(fetchedFamilyAttributesSuccess(attributes));
      dispatch(fetchAttributeGroups(Object.values(attributes).map(attribute => attribute.group)));
    } catch {
      dispatch(setLoadAttributes(false));
      dispatch(fetchedFamilyAttributesFail());
    }
  };
}

export const FETCHED_FAMILY_ATTRIBUTES_SUCCESS = 'FETCHED_FAMILY_ATTRIBUTES_SUCCESS';

export interface FetchedFamilyAttributesSuccessAction {
  type: typeof FETCHED_FAMILY_ATTRIBUTES_SUCCESS;
  attributes: {
    [attributeCode: string]: Attribute;
  };
}

export function fetchedFamilyAttributesSuccess(attributes: {
  [attributeCode: string]: Attribute;
}): FetchedFamilyAttributesSuccessAction {
  return {
    type: FETCHED_FAMILY_ATTRIBUTES_SUCCESS,
    attributes
  };
}

export const FETCHED_FAMILY_ATTRIBUTES_FAIL = 'FETCHED_FAMILY_ATTRIBUTES_FAIL';

export interface FetchedFamilyAttributesFailAction {
  type: typeof FETCHED_FAMILY_ATTRIBUTES_FAIL;
}

export function fetchedFamilyAttributesFail(): FetchedFamilyAttributesFailAction {
  return {
    type: FETCHED_FAMILY_ATTRIBUTES_FAIL
  };
}

export const SET_LOAD_ATTRIBUTES = 'SET_LOAD_ATTRIBUTES';

export interface SetLoadAttributesAction {
  type: typeof SET_LOAD_ATTRIBUTES;
  status: boolean;
}

export function setLoadAttributes(status: boolean): SetLoadAttributesAction {
  return {
    type: SET_LOAD_ATTRIBUTES,
    status
  };
}

export type FetchedFamilyAttributesActions =
  | FetchedFamilyAttributesSuccessAction
  | FetchedFamilyAttributesFailAction
  | SetLoadAttributesAction;
