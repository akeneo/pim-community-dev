import catalogContextReducer, {
  CatalogContextState,
  changeCatalogContextChannel,
  changeCatalogContextLocale,
  initializeCatalogContext,
} from "./catalogContextReducer";
import productEvaluationReducer, {
  getProductEvaluationAction,
  ProductEvaluationState
} from "./productEvaluationReducer";
import productAxesRatesReducer, {getProductAxesRatesAction, ProductAxesRatesState} from "../reducer/productAxesRatesReducer";
import productFamilyInformationReducer, {
  getProductFamilyInformationAction,
  ProductFamilyInformationState
} from "./productFamilyInformationReducer";
import productReducer, {initializeProductAction, ProductState} from "./productReducer";
import pageContextReducer, {
  changeProductTabAction,
  endProductAttributesTabIsLoadedAction,
  PageContextState,
  startProductAttributesTabIsLoadingAction
} from "./pageContextReducer";
import productEditorHighlightReducer, {
  disableWidgetAction,
  disableWidgetHighlightAction,
  enableWidgetAction,
  enableWidgetHighlightAction,
  hidePopoverAction,
  initializePopoverOpeningAction,
  initializeWidgetsListAction,
  ProductEditorHighlightState,
  showPopoverAction,
  showWidgetAction,
  updateWidgetContent,
  updateWidgetContentAnalysis,
  updateWidgetHighlightsAction,
} from "./productEditorHighlightReducer";

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
  PageContextState,
  // Spellcheck
  productEditorHighlightReducer,
  ProductEditorHighlightState,
  initializeWidgetsListAction,
  showWidgetAction,
  enableWidgetAction,
  disableWidgetAction,
  updateWidgetContent,
  updateWidgetContentAnalysis,
  updateWidgetHighlightsAction,
  initializePopoverOpeningAction,
  showPopoverAction,
  hidePopoverAction,
  enableWidgetHighlightAction,
  disableWidgetHighlightAction,
  productAxesRatesReducer,
  ProductAxesRatesState,
  getProductAxesRatesAction
}
