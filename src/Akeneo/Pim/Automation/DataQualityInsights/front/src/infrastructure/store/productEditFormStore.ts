import {applyMiddleware, combineReducers, createStore, Store} from 'redux';
import {composeWithDevTools} from 'redux-devtools-extension';

import {
  AxisRatesState,
  catalogContextReducer,
  CatalogContextState,
  pageContextReducer,
  PageContextState,
  productAxisRatesReducer,
  productEvaluationReducer,
  ProductEvaluationState,
  productFamilyInformationReducer,
  ProductFamilyInformationState,
  productReducer,
  ProductState,
} from "../reducer";

export interface ProductEditFormState {
  catalogContext: CatalogContextState;
  pageContext: PageContextState;
  productAxisRates: AxisRatesState;
  productEvaluation: ProductEvaluationState;
  families: ProductFamilyInformationState;
  product: ProductState;
}

const composeEnhancers = composeWithDevTools({
  name: 'Akeneo PIM / Product Edit Form / Data Quality Insights / Store',
});

const productEditFormStore: Store = createStore(
  combineReducers({
    catalogContext: catalogContextReducer,
    pageContext: pageContextReducer,
    productAxisRates: productAxisRatesReducer,
    productEvaluation: productEvaluationReducer,
    families: productFamilyInformationReducer,
    product: productReducer,
  }),
  composeEnhancers(applyMiddleware()),
);

export default productEditFormStore;
