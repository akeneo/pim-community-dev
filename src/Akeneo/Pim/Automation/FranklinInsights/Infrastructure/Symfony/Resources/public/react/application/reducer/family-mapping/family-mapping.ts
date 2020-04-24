/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {AttributeMappingStatus} from '../../../domain/model/attribute-mapping-status.enum';
import {AttributesMapping} from '../../../domain/model/attributes-mapping';
import {createReducer} from '../../../infrastructure/create-reducer';
import {
  AddAttributeToFamilyActions,
  ATTRIBUTE_ADDED_TO_FAMILY,
  AttributeAddedToFamilyAction
} from '../../action/family-mapping/add-attribute-to-family';
import {
  ATTRIBUTE_CREATED,
  AttributeCreatedAction,
  CreateAttributeActions
} from '../../action/family-mapping/create-attribute';
import {
  DeactivateFranklinAttributeMappingActions,
  FRANKLIN_ATTRIBUTE_MAPPING_DEACTIVATED,
  FranklinAttributeMappingDeactivatedAction
} from '../../action/family-mapping/deactivate-franklin-attribute-mapping';
import {
  FamilyMappingActions,
  FETCHED_FAMILY_MAPPING_FAIL,
  FETCHED_FAMILY_MAPPING_SUCCESS,
  SELECT_FAMILY,
  FetchedFamilyMappingSuccessAction,
  SelectFamilyAction,
  SetLoadFamilyMappingAction,
  SET_LOAD_FAMILY_MAPPING
} from '../../action/family-mapping/family-mapping';
import {MAP_FRANKLIN_ATTRIBUTE, MapFranklinAttributeAction} from '../../../domain/action/map-franklin-attribute';
import {UNMAP_FRANKLIN_ATTRIBUTE, UnmapFranklinAttributeAction} from '../../../domain/action/unmap-franklin-attribute';
import {
  SAVED_FAMILY_MAPPING_FAIL,
  SAVED_FAMILY_MAPPING_SUCCESS,
  SavedFamilyMappingFailAction,
  SavedFamilyMappingSuccessAction
} from '../../action/family-mapping/save-family-mapping';
import {
  APPLY_FRANKLIN_SUGGESTION,
  ApplyFranklinSuggestionAction
} from '../../../domain/action/apply-franklin-suggestion';

interface OriginalMappingState {
  [franklinAttributeCode: string]: {
    attribute: string | null;
    status: AttributeMappingStatus;
  };
}

export interface FamilyMappingState {
  familyCode?: string;
  mapping: AttributesMapping;
  originalMapping: OriginalMappingState;
  loadFamilyMapping: boolean;
}

const selectFamily = (_: FamilyMappingState, action: SelectFamilyAction) => ({
  ...initialState,
  familyCode: action.familyCode
});

const loadFamilyMapping = (state: FamilyMappingState, action: SetLoadFamilyMappingAction) => ({
  ...state,
  loadFamilyMapping: action.status
});

const fetchedFamilyMappingSuccess = (state: FamilyMappingState, action: FetchedFamilyMappingSuccessAction) => ({
  ...state,
  mapping: action.mapping,
  originalMapping: computeOriginalMapping(action.mapping)
});

const fetchedFamilyMappingFail = (state: FamilyMappingState) => ({
  ...state,
  ...initialState
});

const attributeCreated = (state: FamilyMappingState, action: AttributeCreatedAction) => ({
  ...state,
  mapping: {
    ...state.mapping,
    [action.franklinAttributeCode]: {
      ...state.mapping[action.franklinAttributeCode],
      attribute: action.attributeCode,
      status: AttributeMappingStatus.ACTIVE,
      canCreateAttribute: false
    }
  }
});

const attributeAddedToFamily = (state: FamilyMappingState, action: AttributeAddedToFamilyAction) => ({
  ...state,
  mapping: {
    ...state.mapping,
    [action.franklinAttributeCode]: {
      ...state.mapping[action.franklinAttributeCode],
      attribute: action.attributeCode,
      status: AttributeMappingStatus.ACTIVE,
      exactMatchAttributeFromOtherFamily: null
    }
  }
});

const franklinAttributeMappingDeactivated = (
  state: FamilyMappingState,
  action: FranklinAttributeMappingDeactivatedAction
) => ({
  ...state,
  mapping: {
    ...state.mapping,
    [action.franklinAttributeCode]: {
      ...state.mapping[action.franklinAttributeCode],
      attribute: null,
      status: AttributeMappingStatus.INACTIVE
    }
  }
});

const mapFranklinAttribute = (state: FamilyMappingState, action: MapFranklinAttributeAction) => ({
  ...state,
  mapping: {
    ...state.mapping,
    [action.franklinAttributeCode]: {
      ...state.mapping[action.franklinAttributeCode],
      attribute: action.attributeCode,
      status: AttributeMappingStatus.ACTIVE
    }
  }
});

const applyFranklinSuggestion = (state: FamilyMappingState, action: ApplyFranklinSuggestionAction) => ({
  ...state,
  mapping: {
    ...state.mapping,
    [action.franklinAttributeCode]: {
      ...state.mapping[action.franklinAttributeCode],
      attribute: action.pimAttributeCode,
      status: AttributeMappingStatus.ACTIVE
    }
  }
});

const unmapFranklinAttribute = (state: FamilyMappingState, action: UnmapFranklinAttributeAction) => ({
  ...state,
  mapping: {
    ...state.mapping,
    [action.franklinAttributeCode]: {
      ...state.mapping[action.franklinAttributeCode],
      attribute: null,
      status: AttributeMappingStatus.PENDING
    }
  }
});

const savedFamilySuccess = (state: FamilyMappingState) => ({
  ...state,
  originalMapping: computeOriginalMapping(state.mapping)
});

const savedFamilyFail = (state: FamilyMappingState) => state;

const computeOriginalMapping = (mapping: AttributesMapping) => {
  return Object.values(mapping).reduce(
    (originalMapping: OriginalMappingState, {franklinAttribute, attribute, status}) => {
      originalMapping[franklinAttribute.code] = {attribute, status};
      return originalMapping;
    },
    {}
  );
};

const initialState: FamilyMappingState = {
  mapping: {},
  originalMapping: {},
  loadFamilyMapping: false
};

type Actions =
  | FamilyMappingActions
  | CreateAttributeActions
  | AddAttributeToFamilyActions
  | DeactivateFranklinAttributeMappingActions
  | MapFranklinAttributeAction
  | UnmapFranklinAttributeAction
  | SavedFamilyMappingSuccessAction
  | SavedFamilyMappingFailAction
  | ApplyFranklinSuggestionAction
  | SetLoadFamilyMappingAction;

export default createReducer<FamilyMappingState, Actions>(initialState, {
  [SELECT_FAMILY]: selectFamily,
  [FETCHED_FAMILY_MAPPING_SUCCESS]: fetchedFamilyMappingSuccess,
  [FETCHED_FAMILY_MAPPING_FAIL]: fetchedFamilyMappingFail,
  [ATTRIBUTE_CREATED]: attributeCreated,
  [ATTRIBUTE_ADDED_TO_FAMILY]: attributeAddedToFamily,
  [FRANKLIN_ATTRIBUTE_MAPPING_DEACTIVATED]: franklinAttributeMappingDeactivated,
  [MAP_FRANKLIN_ATTRIBUTE]: mapFranklinAttribute,
  [UNMAP_FRANKLIN_ATTRIBUTE]: unmapFranklinAttribute,
  [SAVED_FAMILY_MAPPING_SUCCESS]: savedFamilySuccess,
  [SAVED_FAMILY_MAPPING_FAIL]: savedFamilyFail,
  [APPLY_FRANKLIN_SUGGESTION]: applyFranklinSuggestion,
  [SET_LOAD_FAMILY_MAPPING]: loadFamilyMapping
});
