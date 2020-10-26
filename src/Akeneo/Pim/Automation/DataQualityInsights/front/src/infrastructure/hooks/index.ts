import useFetchDqiDashboardData from "./Dashboard/useFetchDqiDashboardData";
import useGetChartScalingSizeRatio from "./Dashboard/useGetChartScalingSizeRatio";
import useFetchProductDataQualityEvaluation from "./ProductEditForm/useFetchProductDataQualityEvaluation";
import useFetchProductFamilyInformation from "./ProductEditForm/useFetchProductFamilyInformation";
import useCatalogContext from "./ProductEditForm/useCatalogContext";
import useProduct from "./ProductEditForm/useProduct";
import useFetchProductAxisRates from "./ProductEditForm/useFetchProductAxisRates";
import usePageContext from "./ProductEditForm/usePageContext";
import useProductEvaluation from "./ProductEditForm/useProductEvaluation";
import {useProductEvaluatedAttributeGroups} from "./AttributeGroup/useProductEvaluatedAttributeGroups";
import {useFetchKeyIndicators} from "./Dashboard/useFetchKeyIndicators";

export {
  useFetchDqiDashboardData,
  useGetChartScalingSizeRatio as useGetDashboardChartScalingSizeRatio,
  useFetchProductDataQualityEvaluation,
  useFetchProductFamilyInformation,
  useCatalogContext,
  useProduct,
  useFetchProductAxisRates,
  usePageContext,
  useProductEvaluation,
  useProductEvaluatedAttributeGroups,
  useFetchKeyIndicators,
}
