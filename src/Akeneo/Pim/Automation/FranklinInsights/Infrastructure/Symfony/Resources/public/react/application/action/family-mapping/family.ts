import {fetchFamilyLabels} from '../../../infrastructure/fetcher/family';

export function fetchFamily(familyCode: string) {
  return async (dispatch: any) => {
    try {
      const familyLabels = await fetchFamilyLabels(familyCode);
      dispatch(fetchedFamilySuccess(familyCode, familyLabels));
    } catch {
      dispatch(fetchedFamilyFail());
    }
  };
}

export const FETCHED_FAMILY_SUCCESS = 'FETCHED_FAMILY_SUCCESS';

export interface FetchedFamilySuccessAction {
  type: typeof FETCHED_FAMILY_SUCCESS;
  familyCode: string;
  labels: {[locale: string]: string};
}

export function fetchedFamilySuccess(
  familyCode: string,
  labels: {[locale: string]: string}
): FetchedFamilySuccessAction {
  return {
    type: FETCHED_FAMILY_SUCCESS,
    familyCode,
    labels
  };
}

export const FETCHED_FAMILY_FAIL = 'FETCHED_FAMILY_FAIL';

export interface FetchedFamilyFailAction {
  type: typeof FETCHED_FAMILY_FAIL;
}

export function fetchedFamilyFail(): FetchedFamilyFailAction {
  return {
    type: FETCHED_FAMILY_FAIL
  };
}

export type FamilyActions = FetchedFamilySuccessAction | FetchedFamilyFailAction;
