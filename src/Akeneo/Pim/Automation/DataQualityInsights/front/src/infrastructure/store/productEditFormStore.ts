import {applyMiddleware, combineReducers, createStore, Store} from 'redux';
import {composeWithDevTools} from 'redux-devtools-extension';

import {productEditorHighlightReducer, ProductEditorHighlightState} from '../reducer';
import {
  catalogContextReducer,
  CatalogContextState,
  pageContextReducer,
  productEvaluationReducer,
  ProductEvaluationState,
  productFamilyInformationReducer,
  ProductFamilyInformationState,
  productReducer,
  ProductState,
} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/reducer';
import {ProductEditFormPageContextState} from '@akeneo-pim-community/data-quality-insights/src/application/state/PageContextState';

export interface ProductEditFormState {
  catalogContext: CatalogContextState;
  pageContext: ProductEditFormPageContextState;
  productEvaluation: ProductEvaluationState;
  families: ProductFamilyInformationState;
  product: ProductState;
  editorHighlight: ProductEditorHighlightState;
}

const composeEnhancers = composeWithDevTools({
  name: 'Akeneo PIM / Product Edit Form / Data Quality Insights / Store',
});

export const createStoreWithInitialState = (initialState = {}) =>
  createStore(
    combineReducers({
      catalogContext: catalogContextReducer,
      pageContext: pageContextReducer,
      productEvaluation: productEvaluationReducer,
      families: productFamilyInformationReducer,
      product: productReducer,
      editorHighlight: productEditorHighlightReducer,
    }),
    initialState,
    composeEnhancers(applyMiddleware())
  );

const productEditFormStore: Store<any, any> = createStoreWithInitialState();

export default productEditFormStore;
