import {combineReducers} from 'redux';
import {contextReducer, ContextState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
import {structureReducer, StructureState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/structure';
import {productReducer, ProductState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/product';
import {ErrorsState, errorsReducer} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/errors';

export type AssetCollectionState = {
  context: ContextState;
  structure: StructureState;
  product: ProductState;
  errors: ErrorsState;
};

export const assetCollectionReducer = combineReducers({
  context: contextReducer,
  structure: structureReducer,
  product: productReducer,
  errors: errorsReducer,
});
