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
  productReducer, productSpellcheckReducer, ProductSpellcheckState,
  ProductState,
} from "../reducer";

export interface ProductEditFormState {
  catalogContext: CatalogContextState;
  pageContext: PageContextState;
  productEvaluation: ProductEvaluationState;
  families: ProductFamilyInformationState;
  product: ProductState;
  spellcheck: ProductSpellcheckState;
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
    spellcheck: productSpellcheckReducer
  }),
  composeEnhancers(applyMiddleware()),
);

export default productEditFormStore;
