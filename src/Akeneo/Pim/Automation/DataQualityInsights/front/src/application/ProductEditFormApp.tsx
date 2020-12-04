import React, {FunctionComponent} from 'react';
import {Provider} from 'react-redux';
import {productEditFormStore} from '../infrastructure/store';
import {CatalogContextListener, PageContextListener, ProductContextListener} from './listener';
import {Product} from '../domain';
import {fetchProduct, fetchProductDataQualityEvaluation} from '../infrastructure/fetcher';
import {AxisRatesOverviewPortal} from './component/ProductEditForm';
import {AxesContextProvider} from './context/AxesContext';
import {DataQualityInsightsTabContent} from './component/ProductEditForm/TabContent';
import AttributesTabContent from './component/ProductEditForm/TabContent/AttributesTabContent';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import AxisEvaluation from './component/ProductEditForm/TabContent/DataQualityInsights/AxisEvaluation';
import Criterion from './component/ProductEditForm/TabContent/DataQualityInsights/Criterion';
import {
  NotApplicableEnrichmentImageMessage,
  Recommendation,
  ToImproveEnrichmentImageMessage,
} from './component/ProductEditForm/TabContent/DataQualityInsights/Recommendation';
import {followNotApplicableEnrichmentImageRecommendation} from './user-actions';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

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

          <AxesContextProvider axes={['enrichment']}>
            <DataQualityInsightsTabContent
              product={product}
              productEvaluationFetcher={fetchProductDataQualityEvaluation}
            >
              <AxisEvaluation axis={'enrichment'}>
                <Criterion code={'completeness_of_required_attributes'} />
                <Criterion code={'completeness_of_non_required_attributes'} />
                <Criterion code={'enrichment_image'}>
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
            </DataQualityInsightsTabContent>
            <AxisRatesOverviewPortal />
          </AxesContextProvider>
        </Provider>
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export default ProductEditFormApp;
