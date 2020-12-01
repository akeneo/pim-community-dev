import React, {FunctionComponent} from 'react';
import {Provider} from 'react-redux';
import {productEditFormStore} from '../infrastructure/store';
import {Product} from '@akeneo-pim-community/data-quality-insights/src/domain';
import {
  CatalogContextListener,
  PageContextListener,
  ProductContextListener,
} from '@akeneo-pim-community/data-quality-insights/src/application/listener';
import {fetchProduct} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher';
import AttributesTabContent from './component/ProductEditForm/TabContent/AttributesTabContent';
import {DataQualityInsightsTabContent} from '@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm/TabContent';
import AxisEvaluation from '@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm/TabContent/DataQualityInsights/AxisEvaluation';
import {
  Criterion,
  Icon,
} from '@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm/TabContent/DataQualityInsights/Criterion';
import {
  NotApplicableEnrichmentImageMessage,
  Recommendation,
  ToImproveEnrichmentImageMessage,
} from '@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm/TabContent/DataQualityInsights/Recommendation';
import {fetchProductDataQualityEvaluation} from '@akeneo-pim-community/data-quality-insights/src';
import {AxesContextProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/AxesContext';
import {followNotApplicableEnrichmentImageRecommendation} from '@akeneo-pim-community/data-quality-insights/src/application/user-actions';
import {ThemeProvider} from 'styled-components';
import {AssetCollectionIcon, EditIcon, pimTheme, SettingsIcon} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {
  checkFollowingAttributeOptionSpellingCriterionActive,
  checkFollowingAttributeSpellingCriterionActive,
  followAttributeOptionSpellingCriterion,
  followAttributeSpellingCriterion,
} from './user-actions';
import QualityScorePortal from '@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm/QualityScorePortal';

interface ProductEditFormAppProps {
  catalogChannel: string;
  catalogLocale: string;
  product: Product;
}

const ProductEditFormApp: FunctionComponent<ProductEditFormAppProps> = ({product, catalogChannel, catalogLocale}) => {
  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <Provider store={productEditFormStore}>
          <CatalogContextListener catalogChannel={catalogChannel} catalogLocale={catalogLocale} />
          <PageContextListener />
          <ProductContextListener product={product} productFetcher={fetchProduct} />

          <AttributesTabContent product={product} />

          <AxesContextProvider axes={['enrichment', 'consistency']}>
            <DataQualityInsightsTabContent
              product={product}
              productEvaluationFetcher={fetchProductDataQualityEvaluation}
            >
              <AxisEvaluation axis={'enrichment'}>
                <Criterion code={'completeness_of_non_required_attributes'}>
                  <Icon type={EditIcon} />
                </Criterion>
                <Criterion code={'completeness_of_required_attributes'}>
                  <Icon type={EditIcon} />
                </Criterion>
                <Criterion code={'enrichment_image'}>
                  <Icon type={AssetCollectionIcon} />
                  <Recommendation
                    type={'not_applicable'}
                    follow={() => followNotApplicableEnrichmentImageRecommendation(product)}
                  >
                    <NotApplicableEnrichmentImageMessage />
                  </Recommendation>
                  <Recommendation type={'to_improve'}>
                    <ToImproveEnrichmentImageMessage />
                  </Recommendation>
                </Criterion>
              </AxisEvaluation>

              <AxisEvaluation axis={'consistency'}>
                <Criterion code={'consistency_spelling'}>
                  <Icon type={EditIcon} />
                </Criterion>
                <Criterion code={'consistency_textarea_lowercase_words'}>
                  <Icon type={EditIcon} />
                </Criterion>
                <Criterion code={'consistency_textarea_uppercase_words'}>
                  <Icon type={EditIcon} />
                </Criterion>
                <Criterion code={'consistency_text_title_formatting'}>
                  <Icon type={EditIcon} />
                </Criterion>
                <Criterion
                  code={'consistency_attribute_spelling'}
                  followCriterionRecommendation={followAttributeSpellingCriterion}
                  isFollowingCriterionRecommendationAllowed={checkFollowingAttributeSpellingCriterionActive}
                >
                  <Icon type={SettingsIcon} />
                </Criterion>
                <Criterion
                  code={'consistency_attribute_option_spelling'}
                  followCriterionRecommendation={followAttributeOptionSpellingCriterion}
                  isFollowingCriterionRecommendationAllowed={checkFollowingAttributeOptionSpellingCriterionActive}
                >
                  <Icon type={SettingsIcon} />
                </Criterion>
              </AxisEvaluation>
            </DataQualityInsightsTabContent>
            <QualityScorePortal />
          </AxesContextProvider>
        </Provider>
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export default ProductEditFormApp;
