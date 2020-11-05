import React, {FunctionComponent} from 'react';
import {Provider} from 'react-redux';
import {productEditFormStore} from '../infrastructure/store';
import {CatalogContextListener, PageContextListener, ProductContextListener} from './listener';
import {Product} from '../domain';
import fetchProductModel from '../infrastructure/fetcher/ProductEditForm/fetchProductModel';
import {DataQualityInsightsTabContent} from './component/ProductEditForm/TabContent';
import fetchProductModelEvaluation from '../infrastructure/fetcher/ProductEditForm/fetchProductModelEvaluation';
import {AxesContextProvider} from './context/AxesContext';
import AttributesTabContent from './component/ProductEditForm/TabContent/AttributesTabContent';
import {AssetCollectionIcon, EditIcon, pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import AxisEvaluation from './component/ProductEditForm/TabContent/DataQualityInsights/AxisEvaluation';
import {Criterion, Icon} from './component/ProductEditForm/TabContent/DataQualityInsights/Criterion';
import {Recommendation} from './component/ProductEditForm/TabContent/DataQualityInsights/Recommendation';
import {CRITERION_DONE, CRITERION_NOT_APPLICABLE} from '../domain/Evaluation.interface';
import {isSuccess} from './helper';
import {CRITERION_DONE, CRITERION_NOT_APPLICABLE} from "../domain/Evaluation.interface";
import {isSuccess} from "./helper";

const translate = require('oro/translator');

interface ProductModelEditFormAppProps {
  catalogChannel: string;
  catalogLocale: string;
  product: Product;
}

const ProductModelEditFormApp: FunctionComponent<ProductModelEditFormAppProps> = ({
  product,
  catalogChannel,
  catalogLocale,
}) => {
  return (
    <ThemeProvider theme={pimTheme}>
      <Provider store={productEditFormStore}>
        <CatalogContextListener catalogChannel={catalogChannel} catalogLocale={catalogLocale} />
        <PageContextListener />
        <ProductContextListener product={product} productFetcher={fetchProductModel} />

        <AttributesTabContent product={product} />

        <AxesContextProvider axes={['enrichment']}>
          <DataQualityInsightsTabContent product={product} productEvaluationFetcher={fetchProductModelEvaluation} >
            <AxisEvaluation axis={'enrichment'}>
              <Criterion code={'completeness_of_required_attributes'}>
                <Icon type={EditIcon}/>
              </Criterion>
              <Criterion code={'completeness_of_non_required_attributes'}>
                <Icon type={EditIcon}/>
              </Criterion>
              <Criterion code={'enrichment_image'}>
                <Icon type={AssetCollectionIcon}/>
                <Recommendation supports={criterion => criterion.status === CRITERION_NOT_APPLICABLE || (criterion.status === CRITERION_DONE && !isSuccess(criterion.rate) && criterion.improvable_attributes.length === 0)}>
                  <span className="NotApplicableAttribute">{translate('akeneo_data_quality_insights.product_evaluation.messages.add_image_attribute_recommendation')}</span>
                </Recommendation>
              </Criterion>
            </AxisEvaluation>
          </DataQualityInsightsTabContent>
        </AxesContextProvider>
      </Provider>
    </ThemeProvider>
  );
};

export default ProductModelEditFormApp;
