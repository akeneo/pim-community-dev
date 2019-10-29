import {fetchByFamilyCode} from '../../../infrastructure/fetcher/family-mapping';
import {resetGridFilters} from './search-franklin-attributes';
import {fetchAttributes} from './family-attributes';
import {AttributesMapping} from '../../../domain/model/attributes-mapping';
import {unselectAllFranklinAttributes} from './franklin-attribute-selection';
import {fetchFamily} from './family';

export function selectAndFetchFamily(familyCode: string) {
  return (dispatch: any) => {
    dispatch(resetGridFilters());
    dispatch(unselectAllFranklinAttributes());
    dispatch(selectFamily(familyCode));
    dispatch(fetchFamily(familyCode));
    dispatch(fetchFamilyMapping(familyCode));
    dispatch(fetchAttributes(familyCode));
  };
}

export const SELECT_FAMILY = 'SELECT_FAMILY';

export interface SelectFamilyAction {
  type: typeof SELECT_FAMILY;
  familyCode: string;
}

export function selectFamily(familyCode: string): SelectFamilyAction {
  return {
    type: SELECT_FAMILY,
    familyCode
  };
}

export function fetchFamilyMapping(familyCode: string) {
  return async (dispatch: any) => {
    try {
      const mapping = await fetchByFamilyCode(familyCode);
      dispatch(fetchedFamilyMappingSuccess(familyCode, mapping));
    } catch {
      dispatch(fetchedFamilyMappingFail());
    }
  };
}

export const FETCHED_FAMILY_MAPPING_SUCCESS = 'FETCHED_FAMILY_MAPPING_SUCCESS';

export interface FetchedFamilyMappingSuccessAction {
  type: typeof FETCHED_FAMILY_MAPPING_SUCCESS;
  familyCode: string;
  mapping: AttributesMapping;
}

export function fetchedFamilyMappingSuccess(
  familyCode: string,
  mapping: AttributesMapping
): FetchedFamilyMappingSuccessAction {
  return {
    type: FETCHED_FAMILY_MAPPING_SUCCESS,
    familyCode,
    mapping
  };
}

export const FETCHED_FAMILY_MAPPING_FAIL = 'FETCHED_FAMILY_MAPPING_FAIL';

export interface FetchedFamilyMappingFailAction {
  type: typeof FETCHED_FAMILY_MAPPING_FAIL;
}

export function fetchedFamilyMappingFail(): FetchedFamilyMappingFailAction {
  return {
    type: FETCHED_FAMILY_MAPPING_FAIL
  };
}

export type FamilyMappingActions =
  | SelectFamilyAction
  | FetchedFamilyMappingSuccessAction
  | FetchedFamilyMappingFailAction;
