import {createReducer} from '../../../infrastructure/create-reducer';
import {
  FETCHED_FAMILY_MAPPING_FAIL,
  FETCHED_FAMILY_MAPPING_SUCCESS,
  FetchedFamilyMappingFailAction,
  FetchedFamilyMappingSuccessAction
} from '../../action/family-mapping/family-mapping';
import {AttributeMappingStatus} from '../../../domain/model/attribute-mapping-status.enum';
import {FrontAttributeMappingStatus} from '../../../domain/model/front-attribute-mapping-status.enum';
import {
  APPLY_FRANKLIN_SUGGESTION,
  ApplyFranklinSuggestionAction
} from '../../../domain/action/apply-franklin-suggestion';
import {UNMAP_FRANKLIN_ATTRIBUTE, UnmapFranklinAttributeAction} from '../../../domain/action/unmap-franklin-attribute';
import {MAP_FRANKLIN_ATTRIBUTE, MapFranklinAttributeAction} from '../../../domain/action/map-franklin-attribute';
import {
  AddAttributeToFamilyActions,
  ATTRIBUTE_ADDED_TO_FAMILY,
  AttributeAddedToFamilyAction
} from '../../action/family-mapping/add-attribute-to-family';
import {ATTRIBUTE_CREATED, CreateAttributeActions} from '../../action/family-mapping/create-attribute';

export interface AttributesMappingStatusState {
  [franklinAttributeCode: string]: string;
}

const initialState: AttributesMappingStatusState = {};

const initializeAttributeMappingStatus = (
  _: AttributesMappingStatusState,
  action: FetchedFamilyMappingSuccessAction
): AttributesMappingStatusState => {
  return Object.values(action.mapping).reduce(
    (attributesMappingStatusState: AttributesMappingStatusState, {franklinAttribute, status}) => {
      let frontMappingStatus = FrontAttributeMappingStatus.PENDING;
      if (status === AttributeMappingStatus.ACTIVE) {
        frontMappingStatus = FrontAttributeMappingStatus.MAPPED;
      } else if (status === AttributeMappingStatus.PENDING) {
        frontMappingStatus = FrontAttributeMappingStatus.PENDING;
      }
      attributesMappingStatusState[franklinAttribute.code] = frontMappingStatus;

      return attributesMappingStatusState;
    },
    {}
  );
};

const resetAttributesMappingStatus = (): AttributesMappingStatusState => {
  return {};
};

const applyFranklinSuggestion = (state: AttributesMappingStatusState, action: ApplyFranklinSuggestionAction) => ({
  ...state,
  [action.franklinAttributeCode]: FrontAttributeMappingStatus.SUGGESTION_APPLIED
});

const unmapFranklinAttribute = (state: AttributesMappingStatusState, action: UnmapFranklinAttributeAction) => ({
  ...state,
  [action.franklinAttributeCode]: FrontAttributeMappingStatus.PENDING
});

const mapFranklinAttribute = (state: AttributesMappingStatusState, action: MapFranklinAttributeAction) => ({
  ...state,
  [action.franklinAttributeCode]: FrontAttributeMappingStatus.MAPPED
});

const addAttributeToFamily = (state: AttributesMappingStatusState, action: AttributeAddedToFamilyAction) => ({
  ...state,
  [action.franklinAttributeCode]: FrontAttributeMappingStatus.MAPPED
});

const createAttribute = (state: AttributesMappingStatusState, action: AttributeAddedToFamilyAction) => ({
  ...state,
  [action.franklinAttributeCode]: FrontAttributeMappingStatus.MAPPED
});

type Actions =
  | FetchedFamilyMappingSuccessAction
  | FetchedFamilyMappingFailAction
  | ApplyFranklinSuggestionAction
  | UnmapFranklinAttributeAction
  | MapFranklinAttributeAction
  | AddAttributeToFamilyActions
  | CreateAttributeActions;

export default createReducer<AttributesMappingStatusState, Actions>(initialState, {
  [FETCHED_FAMILY_MAPPING_SUCCESS]: initializeAttributeMappingStatus,
  [FETCHED_FAMILY_MAPPING_FAIL]: resetAttributesMappingStatus,
  [APPLY_FRANKLIN_SUGGESTION]: applyFranklinSuggestion,
  [UNMAP_FRANKLIN_ATTRIBUTE]: unmapFranklinAttribute,
  [MAP_FRANKLIN_ATTRIBUTE]: mapFranklinAttribute,
  [ATTRIBUTE_ADDED_TO_FAMILY]: addAttributeToFamily,
  [ATTRIBUTE_CREATED]: createAttribute
});
