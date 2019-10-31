import {fetchAttributesByFamily} from '../../../infrastructure/fetcher/attributes';
import {Attribute} from '../../../domain/model/attribute';
import {fetchAttributeGroups} from './attribute-group';

export function fetchAttributes(familyCode: string) {
  return async (dispatch: any) => {
    try {
      const attributes = await fetchAttributesByFamily(familyCode);
      dispatch(fetchedFamilyAttributesSuccess(attributes));
      dispatch(fetchAttributeGroups(Object.values(attributes).map(attribute => attribute.group)));
    } catch {
      dispatch(fetchedFamilyAttributesFail());
    }
  };
}

export const FETCHED_FAMILY_ATTRIBUTES_SUCCESS = 'FETCHED_FAMILY_ATTRIBUTES_SUCCESS';

export interface FetchedFamilyAttributesSuccessAction {
  type: typeof FETCHED_FAMILY_ATTRIBUTES_SUCCESS;
  attributes: {[attributeCode: string]: Attribute};
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

export type FetchedFamilyAttributesActions = FetchedFamilyAttributesSuccessAction | FetchedFamilyAttributesFailAction;
