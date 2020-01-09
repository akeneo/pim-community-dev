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
import pageContextReducer, {
  changeProductTabAction,
  endProductAttributesTabIsLoadedAction,
  PageContextState,
  startProductAttributesTabIsLoadingAction
} from "./pageContextReducer";
import productSpellcheckReducer, {
  disableWidgetAction,
  enableWidgetAction,
  initializeWidgetsListAction,
  ProductSpellcheckState,
  showWidgetAction,
  updateWidgetContent,
  updateWidgetContentAnalysis,
  updateWidgetEditorOptionsAction,
  updateWidgetHighlightsAction,
  initializePopoverOpeningAction,
  showPopoverAction,
  hidePopoverAction,
} from "./productSpellcheckReducer";

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
  changeProductTabAction,
  startProductAttributesTabIsLoadingAction,
  endProductAttributesTabIsLoadedAction,
  PageContextState,
  // Spellcheck
  productSpellcheckReducer,
  ProductSpellcheckState,
  initializeWidgetsListAction,
  showWidgetAction,
  enableWidgetAction,
  disableWidgetAction,
  updateWidgetContent,
  updateWidgetContentAnalysis,
  updateWidgetHighlightsAction,
  updateWidgetEditorOptionsAction,
  initializePopoverOpeningAction,
  showPopoverAction,
  hidePopoverAction,
}
