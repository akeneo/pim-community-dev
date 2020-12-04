import {applyMiddleware, combineReducers, createStore, Store} from 'redux';
import {composeWithDevTools} from 'redux-devtools-extension';

import {
  catalogContextReducer,
  CatalogContextState,
  pageContextReducer,
  productAxesRatesReducer,
  ProductAxesRatesState,
  productEvaluationReducer,
  ProductEvaluationState,
  productFamilyInformationReducer,
  ProductFamilyInformationState,
  productReducer,
  ProductState,
} from '../reducer';
import {ProductEditFormPageContextState} from '../../application/state/PageContextState';

export interface ProductEditFormState {
  catalogContext: CatalogContextState;
  pageContext: ProductEditFormPageContextState;
  productEvaluation: ProductEvaluationState;
  productAxesRates: ProductAxesRatesState;
  families: ProductFamilyInformationState;
  product: ProductState;
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
      productAxesRates: productAxesRatesReducer,
      families: productFamilyInformationReducer,
      product: productReducer,
    }),
    initialState,
    composeEnhancers(applyMiddleware())
  );

const productEditFormStore: Store<any, any> = createStoreWithInitialState();

export default productEditFormStore;
