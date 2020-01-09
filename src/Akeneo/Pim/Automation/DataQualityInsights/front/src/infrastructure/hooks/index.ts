import useFetchProductDataQualityEvaluation from "./useFetchProductDataQualityEvaluation";
import useFetchProductFamilyInformation from "./useFetchProductFamilyInformation";
import useCatalogContext from "./useCatalogContext";
import useProduct from "./useProduct";
import useFetchProductAxisRates from "./useFetchProductAxisRates";
import usePageContext from "./usePageContext";
import useGetWidgetsList from "./Spellcheck/useGetWidgetsList";
import useGetEditorBoundingRect from "./Spellcheck/useGetEditorBoundingRect";
import useGetEditorScroll from "./Spellcheck/useGetEditorScroll";
import useGetHighlights from "./Spellcheck/useGetHighlights";
import useFetchTextAnalysis from "./Spellcheck/useFetchTextAnalysis";
import useGetPopover from "./Spellcheck/useGetPopover";
import useGetWidget from "./Spellcheck/useGetWidget";

export {
  useFetchProductDataQualityEvaluation,
  useFetchProductFamilyInformation,
  useCatalogContext,
  useProduct,
  useFetchProductAxisRates,
  usePageContext,
  useGetWidgetsList as useGetSpellcheckWidgetsList,
  useGetEditorBoundingRect as useGetSpellcheckEditorBoundingRect,
  useGetEditorScroll as useGetSpellcheckEditorScroll,
  useGetHighlights as useGetSpellcheckHighlights,
  useFetchTextAnalysis as useFetchSpellcheckTextAnalysis,
  useGetPopover as useGetSpellcheckPopover,
  useGetWidget as useGetSpellcheckWidget,
}
