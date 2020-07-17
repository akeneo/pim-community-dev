import useFetchDqiDashboardData from "./useFetchDqiDashboardData";
import useFetchProductDataQualityEvaluation from "./useFetchProductDataQualityEvaluation";
import useFetchProductFamilyInformation from "./useFetchProductFamilyInformation";
import useCatalogContext from "./useCatalogContext";
import useProduct from "./useProduct";
import useFetchProductAxisRates from "./useFetchProductAxisRates";
import usePageContext from "./usePageContext";
import useProductEvaluation from "./useProductEvaluation";
import useGetWidgetsList from "./EditorHighlight/useGetWidgetsList";
import useGetEditorBoundingRect from "./EditorHighlight/useGetEditorBoundingRect";
import useGetEditorScroll from "./EditorHighlight/useGetEditorScroll";
import useGetHighlights from "./EditorHighlight/useGetHighlights";
import useFetchTextAnalysis from "./EditorHighlight/Spellcheck/useFetchTextAnalysis";
import useGetPopover from "./EditorHighlight/useGetPopover";
import useGetWidget from "./EditorHighlight/useGetWidget";
import useFetchIgnoreTextIssue from "./EditorHighlight/Spellcheck/useFetchIgnoreTextIssue";
import useGetChartScalingSizeRatio from "./Dashboard/useGetChartScalingSizeRatio";

export {
  useFetchDqiDashboardData,
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
  useGetChartScalingSizeRatio as useGetDashboardChartScalingSizeRatio,
  useProductEvaluation,
}
