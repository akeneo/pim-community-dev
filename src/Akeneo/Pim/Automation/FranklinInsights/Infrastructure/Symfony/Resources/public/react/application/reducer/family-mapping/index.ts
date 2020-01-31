import {combineReducers} from 'redux';
import familyMappingReducer from './family-mapping';
import franklinAttributeSelectionReducer from './franklin-attribute-selection';
import searchFranklinAttributesReducer from './search-franklin-attributes';
import attributesReducer from './attributes';
import attributeGroupsReducer from './attribute-groups';
import familyReducer from './family';
import attributeMappingStatusReducer from './attributes-mapping-status';

const rootReducer = combineReducers({
  familyMapping: familyMappingReducer,
  searchFranklinAttributes: searchFranklinAttributesReducer,
  selectedFranklinAttributeCodes: franklinAttributeSelectionReducer,
  attributes: attributesReducer,
  attributeGroups: attributeGroupsReducer,
  family: familyReducer,
  attributesMappingStatus: attributeMappingStatusReducer
});

export type FamilyMappingState = ReturnType<typeof rootReducer>;

export default rootReducer;
