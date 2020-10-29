import catalogContextReducer, {
  CatalogContextState,
  changeCatalogContextChannel,
  changeCatalogContextLocale,
  initializeCatalogContext,
} from './ProductEditForm/catalogContextReducer';
import productEvaluationReducer, {
  getProductEvaluationAction,
  ProductEvaluationState,
} from './ProductEditForm/productEvaluationReducer';
import productAxesRatesReducer, {
  getProductAxesRatesAction,
  ProductAxesRatesState,
} from './ProductEditForm/productAxesRatesReducer';
import productFamilyInformationReducer, {
  getProductFamilyInformationAction,
  ProductFamilyInformationState,
} from './ProductEditForm/productFamilyInformationReducer';
import productReducer, {initializeProductAction, ProductState} from './ProductEditForm/productReducer';
import pageContextReducer, {
  changeProductTabAction,
  endProductAttributesTabIsLoadedAction,
  showDataQualityInsightsAttributeToImproveAction,
  startProductAttributesTabIsLoadingAction,
} from './ProductEditForm/pageContextReducer';

export {
  // Catalog Context Reducer
  catalogContextReducer,
  changeCatalogContextLocale,
  changeCatalogContextChannel,
  initializeCatalogContext,
  CatalogContextState,
  // Product Evaluation Reducer
  productEvaluationReducer,
  getProductEvaluationAction,
  ProductEvaluationState,
  // Product Evaluation Reducer
  productFamilyInformationReducer,
  getProductFamilyInformationAction,
  ProductFamilyInformationState,
  // Product
  productReducer,
  initializeProductAction,
  ProductState,
  // Page Context Reducer
  pageContextReducer,
  changeProductTabAction,
  startProductAttributesTabIsLoadingAction,
  endProductAttributesTabIsLoadedAction,
  showDataQualityInsightsAttributeToImproveAction,
  productAxesRatesReducer,
  ProductAxesRatesState,
  getProductAxesRatesAction,
};
