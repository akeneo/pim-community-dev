import {fetchByFamilyCode} from '../../../infrastructure/fetcher/family-mapping';
import {resetGridFilters} from './search-franklin-attributes';
import {fetchAttributes} from './family-attributes';
import {AttributesMapping} from '../../../domain/model/attributes-mapping';
import {unselectAllFranklinAttributes} from './franklin-attribute-selection';
import {fetchFamily} from './family';
import {AttributeMapping} from '../../../domain/model/attribute-mapping';
import {AttributeMappingStatus} from '../../../domain/model/attribute-mapping-status.enum';
import {applyFranklinSuggestion} from '../../action-creator/family-mapping/apply-franklin-suggestion';
import {notify} from '../notify';
import {NotificationLevel} from '../../notification-level';
import {translate} from '../../../infrastructure/translator';

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
      dispatch(setLoadFamilyMapping(true));
      const mapping = await fetchByFamilyCode(familyCode);
      dispatch(setLoadFamilyMapping(false));
      dispatch(fetchedFamilyMappingSuccess(familyCode, mapping));

      Object.values(mapping).forEach((attributeMapping: AttributeMapping) => {
        if (attributeMapping.status === AttributeMappingStatus.PENDING && attributeMapping.suggestions.length > 0) {
          dispatch(
            applyFranklinSuggestion(
              familyCode,
              attributeMapping.franklinAttribute.code,
              attributeMapping.suggestions[0]
            )
          );
        }
      });
    } catch {
      dispatch(setLoadFamilyMapping(false));
      dispatch(fetchedFamilyMappingFail());
      dispatch(
        notify(
          NotificationLevel.ERROR,
          translate('akeneo_franklin_insights.entity.attributes_mapping.flash.fail_to_retrieve_franklin_mapping')
        )
      );
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

export const SET_LOAD_FAMILY_MAPPING = 'SET_LOAD_FAMILY_MAPPING';

export interface SetLoadFamilyMappingAction {
  type: typeof SET_LOAD_FAMILY_MAPPING;
  status: boolean;
}

export function setLoadFamilyMapping(status: boolean): SetLoadFamilyMappingAction {
  return {
    type: SET_LOAD_FAMILY_MAPPING,
    status
  };
}

export type FamilyMappingActions =
  | SelectFamilyAction
  | FetchedFamilyMappingSuccessAction
  | FetchedFamilyMappingFailAction;
