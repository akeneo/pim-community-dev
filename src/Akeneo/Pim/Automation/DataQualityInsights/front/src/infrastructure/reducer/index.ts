import catalogContextReducer, {
  CatalogContextState,
  changeCatalogContextChannel,
  changeCatalogContextLocale,
  initializeCatalogContext,
} from "./catalogContextReducer";
import productAxisRatesReducer, {AxisRatesState, getProductAxisRatesAction} from "./productAxisRatesReducer";
import productEvaluationReducer, {getProductEvaluationAction, ProductEvaluationState} from "./productEvaluationReducer";
import productFamilyInformationReducer, {
  getProductFamilyInformationAction,
  ProductFamilyInformationState
} from "./productFamilyInformationReducer";
import productReducer, {initializeProductAction, ProductState} from "./productReducer";
import pageContextReducer, {changeDataQualityInsightsTabContentVisibility, PageContextState} from "./pageContextReducer";

export {
  // Catalog Context Reducer
  catalogContextReducer,
  changeCatalogContextLocale,
  changeCatalogContextChannel,
  initializeCatalogContext,
  CatalogContextState,
  // Product Axis Rates Reducer
  productAxisRatesReducer,
  getProductAxisRatesAction,
  AxisRatesState,
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
  changeDataQualityInsightsTabContentVisibility,
  PageContextState,
}
