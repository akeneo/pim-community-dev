import {combineReducers} from 'redux';
import {contextReducer, ContextState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
import {structureReducer, StructureState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/structure';
import {valuesReducer, ValuesState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';
import {ErrorsState, errorsReducer} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/errors';

export type AssetCollectionState = {
  context: ContextState;
  structure: StructureState;
  values: ValuesState;
  errors: ErrorsState;
};

export const assetCollectionReducer = combineReducers({
  context: contextReducer,
  structure: structureReducer,
  values: valuesReducer,
  errors: errorsReducer,
});
