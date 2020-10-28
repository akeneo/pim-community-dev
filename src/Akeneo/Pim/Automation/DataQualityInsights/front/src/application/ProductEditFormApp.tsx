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
import Criterion from "./component/ProductEditForm/TabContent/DataQualityInsights/Criterion";
import {Recommendation} from "./component/ProductEditForm/TabContent/DataQualityInsights/Recommendation";
import {CRITERION_DONE, CRITERION_NOT_APPLICABLE} from "../domain/Evaluation.interface";

const translate = require('oro/translator');

interface ProductEditFormAppProps {
  catalogChannel: string;
  catalogLocale: string;
  product: Product;
}

const ProductEditFormApp: FunctionComponent<ProductEditFormAppProps> = ({product, catalogChannel, catalogLocale}) => {
  return (
    <ThemeProvider theme={pimTheme}>
      <Provider store={productEditFormStore}>
        <CatalogContextListener catalogChannel={catalogChannel} catalogLocale={catalogLocale} />
        <PageContextListener />
        <ProductContextListener product={product} productFetcher={fetchProduct}/>

        <AttributesTabContent product={product}/>

        <AxesContextProvider axes={['enrichment']}>
          <DataQualityInsightsTabContent product={product} productEvaluationFetcher={fetchProductDataQualityEvaluation}>
            <AxisEvaluation axis={'enrichment'}>
              <Criterion code={'completeness_of_required_attributes'} />
              <Criterion code={'completeness_of_non_required_attributes'} />
              <Criterion code={'missing_image_attribute'}>
                <Recommendation supports={criterion => criterion.status === CRITERION_NOT_APPLICABLE || (criterion.status === CRITERION_DONE && criterion.improvable_attributes.length === 0)}>
                  <span className="NotApplicableAttribute">{translate('akeneo_data_quality_insights.product_evaluation.messages.add_image_attribute_recommendation')}</span>
                </Recommendation>
              </Criterion>
            </AxisEvaluation>
          </DataQualityInsightsTabContent>
          <AxisRatesOverviewPortal />
        </AxesContextProvider>
      </Provider>
    </ThemeProvider>
  );
};

export default ProductEditFormApp;
