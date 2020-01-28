import catalogContextReducer, {
  CatalogContextState,
  changeCatalogContextChannel,
  changeCatalogContextLocale,
  initializeCatalogContext,
} from "./catalogContextReducer";
import productEvaluationReducer, {
  getProductEvaluationAction,
  getProductEvaluationRatesAction,
  ProductEvaluationState
} from "./productEvaluationReducer";
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
  updateWidgetTitleSuggestion,
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
  getProductEvaluationRatesAction,
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
  updateWidgetTitleSuggestion,
  initializePopoverOpeningAction,
  showPopoverAction,
  hidePopoverAction,
  enableWidgetHighlightAction,
  disableWidgetHighlightAction,
}
