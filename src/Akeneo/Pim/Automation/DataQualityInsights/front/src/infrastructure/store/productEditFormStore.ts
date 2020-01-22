import {applyMiddleware, combineReducers, createStore, Store} from 'redux';
import {composeWithDevTools} from 'redux-devtools-extension';

import {
  catalogContextReducer,
  CatalogContextState,
  pageContextReducer,
  PageContextState,
  productEvaluationReducer,
  ProductEvaluationState,
  productFamilyInformationReducer,
  ProductFamilyInformationState,
  productReducer, productEditorHighlightReducer, ProductEditorHighlightState,
  ProductState,
} from "../reducer";

export interface ProductEditFormState {
  catalogContext: CatalogContextState;
  pageContext: PageContextState;
  productEvaluation: ProductEvaluationState;
  families: ProductFamilyInformationState;
  product: ProductState;
  editorHighlight: ProductEditorHighlightState;
}

const composeEnhancers = composeWithDevTools({
  name: 'Akeneo PIM / Product Edit Form / Data Quality Insights / Store',
});

const productEditFormStore: Store = createStore(
  combineReducers({
    catalogContext: catalogContextReducer,
    pageContext: pageContextReducer,
    productEvaluation: productEvaluationReducer,
    families: productFamilyInformationReducer,
    product: productReducer,
    editorHighlight: productEditorHighlightReducer
  }),
  composeEnhancers(applyMiddleware()),
);

export default productEditFormStore;
