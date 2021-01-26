import fetchDqiDashboardData from './Dashboard/fetchDqiDashboardData';
import fetchProductDataQualityEvaluation from './ProductEditForm/fetchProductDataQualityEvaluation';
import fetchFamilyInformation from './ProductEditForm/fetchFamilyInformation';
import fetchProduct from './ProductEditForm/fetchProduct';
import {fetchAllAttributeGroupsDqiStatus} from './AttributeGroup/attributeGroupDqiStatusFetcher';
import {fetchAttributeGroupsByCode} from './AttributeGroup/attributeGroupsFetcher';
import {fetchKeyIndicators} from './Dashboard/fetchKeyIndicators';
import {fetchQualityScoreEvolution} from './Dashboard/fetchQualityScoreEvolution';

export {
  fetchDqiDashboardData,
  fetchProductDataQualityEvaluation,
  fetchFamilyInformation,
  fetchProduct,
  fetchAllAttributeGroupsDqiStatus,
  fetchAttributeGroupsByCode,
  fetchKeyIndicators,
  fetchQualityScoreEvolution,
};
