import useFetchProductDataQualityEvaluation from "./ProductEditForm/useFetchProductDataQualityEvaluation";
import useFetchProductFamilyInformation from "./ProductEditForm/useFetchProductFamilyInformation";
import useCatalogContext from "./ProductEditForm/useCatalogContext";
import useProduct from "./ProductEditForm/useProduct";
import useFetchProductAxisRates from "./ProductEditForm/useFetchProductAxisRates";
import usePageContext from "./ProductEditForm/usePageContext";
import useProductEvaluation from "./ProductEditForm/useProductEvaluation";
import useGetWidgetsList from "./EditorHighlight/useGetWidgetsList";
import useGetEditorBoundingRect from "./EditorHighlight/useGetEditorBoundingRect";
import useGetEditorScroll from "./EditorHighlight/useGetEditorScroll";
import useGetHighlights from "./EditorHighlight/useGetHighlights";
import useFetchTextAnalysis from "./EditorHighlight/Spellcheck/useFetchTextAnalysis";
import useGetPopover from "./EditorHighlight/useGetPopover";
import useGetWidget from "./EditorHighlight/useGetWidget";
import useFetchIgnoreTextIssue from "./EditorHighlight/Spellcheck/useFetchIgnoreTextIssue";

export {
  useFetchProductDataQualityEvaluation,
  useFetchProductFamilyInformation,
  useCatalogContext,
  useProduct,
  useFetchProductAxisRates,
  usePageContext,
  useGetWidgetsList as useGetEditorHighlightWidgetsList,
  useGetEditorBoundingRect as useGetEditorHighlightBoundingRect,
  useGetEditorScroll as useGetEditorHighlightScroll,
  useGetHighlights as useGetEditorHighlights,
  useFetchTextAnalysis as useFetchSpellcheckTextAnalysis,
  useGetPopover as useGetEditorHighlightPopover,
  useGetWidget as useGetSpellcheckWidget,
  useFetchIgnoreTextIssue,
  useProductEvaluation,
}
